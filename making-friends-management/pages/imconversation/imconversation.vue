<template>
	<view class="p-5">
		<search-bar placeholder="最后一条消息" @search="submitSearch" />
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.ImConversation/delete,POST'">
			<el-popconfirm :title="'是否要批量删除选中'+title+'?'" @confirm="deleteItem(ids)">
				<template #reference>
					<el-button type="danger">批量删除</el-button>
				</template>
			</el-popconfirm>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" @selection-change="handleSelectionChange" ref="table"
			height="500">
			<el-table-column type="selection" width="55" />
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column prop="last_msg_note" label="最后一条消息" width="150" />
			<el-table-column label="未读数" width="100">
				<template #default="{ row }">
					<el-tag :type="row.unread_count > 0 ? 'warning' : 'info'" size="small">{{ row.unread_count }}</el-tag> 
				</template>
			</el-table-column>
			<el-table-column label="发送人" width="140">
				<template #default="{ row }">
					<user-info :status="row.user_status" :avatar="row.user_avatar" :name="row.user_name" />
				</template>
			</el-table-column>
			<el-table-column label="接收人" width="140" >
				<template #default="{ row }">
					<user-info :status="row.target_status" :avatar="row.target_avatar" :name="row.target_name" />
				</template>
			</el-table-column>
			<el-table-column prop="update_time" label="最新消息时间" width="220" />

			<el-table-column fixed="right" label="操作" width="200">
				<template #default="{ row,$index }">
					<span v-permission="'admin.ImConversation/delete,POST'">
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
		<pagination v-model="current_page" :page-count="last_page" :page-size="per_page" @update:modelValue="loadData()" />
	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: "聊天会话",

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
				
				state_text:{
					100:"发送成功",
					101:"对方已把你拉黑",
					102:"你把对方拉黑了",
					103:"对方已被系统封禁",
					104:"禁止发送",
				},
				state_color:{
					100:"success",
					101:"danger",
					102:"danger",
					103:"danger",
					104:"danger",
				},
				
				type_text:{
					"text":"文字"
				}
			}
		},
		onLoad() {
			this.loadData()
		},
		methods: {
			submitSearch(keyword) {
				this.page = 1
				this.loadData(keyword)
			},
			// 多选
			handleSelectionChange(e) {
				this.ids = e.map(o => o.id)
			},
			// 加载数据
			loadData(keyword = "") {
				this.$loading.show()
				this.$api.getImconversationList(this.current_page, keyword)
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
				this.$api.deleteImconversation(id)
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