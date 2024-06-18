<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3">
			<el-checkbox v-model="only_nocheck" style="margin-right: 10px;" @change="loadData">只显示待审核</el-checkbox>
			<view v-permission="'admin.IpImage/update,POST'" class="mr-1">
				<el-popconfirm :title="'是否要批量保留选中图片?'" @confirm="updateItem(ids,1)">
					<template #reference>
						<el-button type="success">批量保留</el-button>
					</template>
				</el-popconfirm>
				<el-popconfirm :title="'是否要批量删除选中图片?'" @confirm="updateItem(ids,0)">
					<template #reference>
						<el-button type="danger">批量删除图片</el-button>
					</template>
				</el-popconfirm>
			</view>

			<el-button v-permission="'admin.IpBlacklist/index,GET'" type="primary" @click.stop="openIpBlacklist">ip黑名单</el-button>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" @selection-change="handleSelectionChange" ref="table"
			height="500">
			<el-table-column type="selection" width="55" />
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column prop="url" label="url" width="150">
				<template #default="{ row,$index }">
					<el-image style="width: 120px; height: 120px" :src="row.url + '?v=' + Math.random()" fit="contain" :preview-src-list="srcList" :initial-index="$index" :zIndex="10000" preview-teleported/>
				</template>
			</el-table-column>
			<el-table-column prop="status" label="状态" width="80">
				<template #default="{ row }">
					<el-tag size="small" :type="status_color[row.status]">{{ status_text[row.status] }}</el-tag>
				</template>
			</el-table-column>
			<el-table-column align="center" prop="IP" label="IP地址" width="150">
				<template #default="{ row }">
					<el-button text type="primary" size="small" @click="addIpBlacklist(row.ip)"
						:disabled="row.in_ip_blacklist">{{ row.ip }}</el-button>
				</template>
			</el-table-column>
			<el-table-column label="用户" width="120">
				<template #default="{ row }">
					<user-info :status="row.user_status" :avatar="row.avatar" :name="row.name" />
				</template>
			</el-table-column>
			<el-table-column prop="create_time" label="记录时间" width="220" />

			<el-table-column fixed="right" label="操作" width="200">
				<template #default="{ row,$index }">
					<!-- <el-button link type="primary">请求参数</el-button> -->
					<view v-permission="'admin.IpImage/update,POST'" v-if="row.status == 2">
						<el-popconfirm :title="'是否要保留该图片?'" @confirm="updateItem(row.id,1)">
							<template #reference>
								<el-button link type="success">保留</el-button>
							</template>
						</el-popconfirm>
						<el-popconfirm :title="'是否要删除该图片?'" @confirm="updateItem(row.id,0)">
							<template #reference>
								<el-button link type="danger">删除图片</el-button>
							</template>
						</el-popconfirm>
					</view>
					<view v-else v-permission="'admin.IpImage/delete,POST'">
						<el-popconfirm :title="'是否要删除该记录?'" @confirm="deleteItem(row.id)">
							<template #reference>
								<el-button link type="danger">删除</el-button>
							</template>
						</el-popconfirm>
					</view>
				</template>
			</el-table-column>
		</el-table>

		<!-- 分页 -->
		<pagination v-model="current_page" :page-count="last_page" :page-size="per_page"
			@update:modelValue="loadData()" />


		<ip-blacklist ref="IpBlacklist" @reload="loadData"></ip-blacklist>

	</view>
</template>

<script>
	import IpBlacklist from "../user_action_log/ip_blacklist.vue"
	export default {
		components: {
			IpBlacklist
		},
		data() {
			return {
				title: "图片审核",

				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,
				ids: [],
				
				status_color:{
					0:"danger",
					1:"info",
					2:""
				},
				status_text:{
					0:"删除",
					1:"保留",
					2:"审核中"
				},
				
				only_nocheck:true
			}
		},
		computed: {
			srcList() {
				return this.data.map(o=>o.url) 
			}
		},
		onLoad() {
			this.loadData()
		},
		methods: {
			openIpBlacklist() {
				this.$refs["IpBlacklist"].open()
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
			// 多选
			handleSelectionChange(e) {
				this.ids = e.map(o => o.id)
			},
			// 加载数据
			loadData() {
				this.$loading.show()
				this.$api.getIpImageList(this.current_page,this.only_nocheck)
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
			// 审核/批量审核
			updateItem(id,status) {
				if (Array.isArray(id) && id.length == 0) {
					return this.$warning("请先选择要是审核的" + this.title)
				}
				this.$loading.show()
				this.$api.updateIpImages(id,status)
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
			// 删除/批量删除
			deleteItem(id) {
				if (Array.isArray(id) && id.length == 0) {
					return this.$warning("请先选择要删除的" + this.title)
				}
				this.$loading.show()
				this.$api.deleteIpImage(id)
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