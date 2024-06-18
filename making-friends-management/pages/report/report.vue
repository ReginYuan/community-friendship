<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.Report/delete,POST'">
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
			<el-table-column prop="name" label="举报人" width="160">
				<template #default="{ row }">
					<user-info badgeText="封禁" :status="row.user_status" :avatar="row.avatar" :name="row.name"/>
				</template>
			</el-table-column>
			<el-table-column prop="name" label="被举报人" width="160">
				<template #default="{ row }">
					<user-info badgeText="封禁" :status="row.report_user_status" :avatar="row.report_avatar" :name="row.report_name"/>
				</template>
			</el-table-column>
			<el-table-column align="center" prop="content" label="举报原因" width="150" />
			<el-table-column align="center" prop="state" label="处理结果" width="120">
				<template #default="{ row }">
					<el-tag :type="state_colors[row.state]" size="small">{{ state_options[row.state] }}</el-tag>
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
					<el-button v-permission="'admin.Report/update,POST'" v-if="row.state == 'pending'" link type="primary" @click="openEdit(row)">编辑</el-button>
					<span v-permission="'admin.Report/delete,POST'">
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

		<!-- 创建/编辑表单 -->
		<FormDrawer ref="formRef" :title="title" :model="form" :rules="rules" @success="handleSubmitSuccess" :updateApiFun="$api.updateReport">
			<el-form-item label="处理结果" prop="state">
				<el-radio-group v-model="form.state" class="ml-4">
				  <el-radio label="success">封号</el-radio>
				  <el-radio label="fail">驳回</el-radio>
				</el-radio-group>
			</el-form-item>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title:"举报",
				
				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,
				ids: [],

				state_options:{
					"pending":"等待处理",
					"success":"成功",
					"fail":"驳回"
				},
				state_colors:{
					"pending":"",
					"success":"success",
					"fail":"danger"
				},

				// 表单
				form: {
					state:""
				},
				// 验证规则
				rules: {

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
				this.$api.getReportList(this.current_page, this.keyword)
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
				this.$api.deleteReport(id)
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
			// 打开创建表单
			openCreate(){
				this.$refs.formRef.open(this.form)
			},
			// 打开编辑表单
			openEdit(row){
				this.$refs.formRef.open(this.form,row)
			},
			// 修改/创建成功
			handleSubmitSuccess(editId) {
				// 修改刷新当前页，新增刷新第一页
				if(!editId){
					this.current_page = 1
				}
				this.loadData()
			}
		}
	}
</script>

<style>

</style>