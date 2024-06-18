<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.Feedback/delete,POST'">
			<el-popconfirm :title="'是否要批量删除选中'+title+'?'" @confirm="deleteItem(ids)">
				<template #reference>
					<el-button type="danger">批量删除</el-button>
				</template>
			</el-popconfirm>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" @selection-change="handleSelectionChange" ref="table">
			<el-table-column type="selection" width="55" />
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column prop="content" label="反馈" width="350">
				<template #default="{ row }">
					<feedback-item :item="row"></feedback-item>
				</template>
			</el-table-column>
			<el-table-column align="center" prop="type" label="类型" width="120">
				<template #default="{ row }">
					<el-tag :type="type_colors[row.type]" size="small">{{ type_options[row.type] }}</el-tag>
				</template>
			</el-table-column>
			<el-table-column prop="创建/更新时间" label="创建时间" width="220">
				<template #default="{ row }">
					<view class="mt-0.5 text-gray-700">
						<p>创建：{{ row.create_time }}</p>
						<p>更新：{{ row.update_time }}</p>
					</view>
				</template>
			</el-table-column>

			<el-table-column fixed="right" label="操作" width="120">
				<template #default="{ row,$index }">
					<span v-permission="'admin.Feedback/delete,POST'">
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
				title:"反馈",

				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,
				ids: [],
				
				type_options:{
					"user":"用户反馈",
					"worker":"系统回复",
				},
				type_colors:{
					"user":"",
					"worker":"success",
				},
			}
		},
		onLoad() {
			this.loadData()
		},
		methods: {
			// 多选
			handleSelectionChange(e) {
				this.ids = e.map(o => o.id)
			},
			// 加载数据
			loadData() {
				this.$loading.show()
				this.$api.getFeedbackList(this.current_page)
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
				this.$api.deleteFeedback(id)
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