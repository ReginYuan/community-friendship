<template>
	<view class="p-5">
		<search-bar placeholder="ip/url/记录/设备" @search="submitSearch" />
		<!-- 操作按钮 -->
		<view class="flex py-3">
			<span v-permission="'admin.UserActionLog/delete,POST'">
				<el-popconfirm :title="'是否要批量删除选中'+title+'?'" @confirm="deleteItem(ids)">
					<template #reference>
						<el-button type="danger">批量删除</el-button>
					</template>
				</el-popconfirm>
			</span>
			<el-button class="ml-1" v-permission="'admin.IpBlacklist/index,GET'" type="primary" @click.stop="openIpBlacklist">ip黑名单</el-button>
			<el-button v-permission="'admin.UserActionLog/clear,POST'" text type="danger" @click.stop="clearLog">清空日志</el-button>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" @selection-change="handleSelectionChange" ref="table"
			height="500">
			<el-table-column type="selection" width="55" />
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column align="center" prop="method" label="请求方式" width="100">
				<template #default="{ row }">
					<el-tag :type="method_colors[row.method]" size="small">{{ row.method }}</el-tag>
				</template>
			</el-table-column>
			<el-table-column align="center" prop="IP" label="IP地址" width="150">
				<template #default="{ row }">
					<el-button text type="primary" size="small" @click="addIpBlacklist(row.ip)"
						:disabled="row.in_ip_blacklist">{{ row.ip }}</el-button>
				</template>
			</el-table-column>
			<el-table-column prop="url" label="url" width="180" />
			<el-table-column prop="notes" label="记录" width="180">
				<template #default="{ row }">
					<el-alert :title="row.notes || '无'" :type="row.type" :closable="false" />
				</template>
			</el-table-column>
			<el-table-column label="用户" width="120">
				<template #default="{ row }">
					<user-info :status="row.user_status" :avatar="row.avatar" :name="row.name" />
				</template>
			</el-table-column>
			<el-table-column prop="user_agent" label="设备" width="200">
				<template #default="{ row }">
					<span style="font-size: 12px;">{{ row.user_agent }}</span>
				</template>
			</el-table-column>
			<el-table-column prop="create_time" label="记录时间" width="220" />

			<el-table-column fixed="right" label="操作" width="200">
				<template #default="{ row,$index }">
					<el-button link type="primary" @click="openParamDialog(row)">请求参数</el-button>
					<span v-permission="'admin.UserActionLog/delete,POST'">
						<el-popconfirm :title="'是否要删除该'+title+'?'" @confirm="deleteItem(row.id)">
							<template #reference>
								<el-button link type="danger">删除</el-button>
							</template>
						</el-popconfirm>
					</span>
				</template>
			</el-table-column>
		</el-table>

		<!-- 分页 -->
		<pagination v-model="current_page" :page-count="last_page" :page-size="per_page"
			@update:modelValue="loadData()" />

		<el-dialog v-model="param_dialog" title="请求参数" width="700">
			<pre
				style="white-space: pre-wrap;width:100%;overflow: auto;background-color: #f4f4f4;padding: 20px;box-sizing: border-box;user-select: text;font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;">{{ param }}
			</pre>
		</el-dialog>

		<ip-blacklist ref="IpBlacklist" @reload="loadData"></ip-blacklist>

	</view>
</template>

<script>
	import IpBlacklist from "./ip_blacklist.vue"
	export default {
		components: {
			IpBlacklist
		},
		data() {
			return {
				title: "反馈",

				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,
				ids: [],

				method_colors: {
					"GET": "success",
					"POST": "",
				},

				param_dialog: false,
				param: null
			}
		},
		onLoad() {
			this.loadData()
		},
		methods: {
			clearLog() {
				this.$prompt('输入 “清空日志” 执行清空日志任务', '提示', {
					confirmButtonText: '清空',
					cancelButtonText: '取消',
					inputPattern: /^清空日志$/,
					inputErrorMessage: '指令错误，请输入 “清空日志”',
				})
				.then(({
					value
				}) => {
					this.$loading.show()
					this.$api.clearUserActionLog()
					.then((res) => {
						this.$refs.table.clearSelection()
						this.$success(res.msg)
						// 加载当前页
						this.loadData()
					})
					.finally(() => {
						this.$loading.hide()
					})
				})
			},
			openIpBlacklist() {
				this.$refs["IpBlacklist"].open()
			},
			submitSearch(keyword) {
				this.page = 1
				this.loadData(keyword)
			},
			// 添加ip黑名单
			addIpBlacklist(ip) {
				this.$confirm(
						'将该ip加入黑名单?',
						'提示', {
							confirmButtonText: '确定',
							cancelButtonText: '取消',
							type: 'warning',
						}
					)
					.then(() => {
						this.$loading.show()
						this.$api.addIpBlacklist(ip)
							.then((res) => {
								this.$success(res.msg)
								// 加载当前页
								this.loadData()
							})
							.finally(() => {
								this.$loading.hide()
							})
					})

			},
			openParamDialog(row) {
				this.param = row.param
				this.param_dialog = true
			},
			// 多选
			handleSelectionChange(e) {
				this.ids = e.map(o => o.id)
			},
			// 加载数据
			loadData(keyword = "") {
				this.$loading.show()
				this.$api.getUserActionLogList(this.current_page, keyword)
					.then(({
						data
					}) => {
						this.data = data.data
						this.total = data.total
						this.per_page = data.per_page
						this.current_page = data.current_page
						this.last_page = data.last_page
					})
					.finally(() => {
						this.$loading.hide()
					})
			},
			// 删除/批量删除
			deleteItem(id) {
				if (Array.isArray(id) && id.length == 0) {
					return this.$warning("请先选择要删除的" + this.title)
				}
				this.$loading.show()
				this.$api.deleteUserActionLog(id)
					.then((res) => {
						this.$refs.table.clearSelection()
						this.$success(res.msg)
						// 加载当前页
						this.loadData()
					})
					.finally(() => {
						this.$loading.hide()
					})
			},
		}
	}
</script>

<style>

</style>