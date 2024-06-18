<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.Category/save,POST'">
			<el-button type="primary" @click="openCreate">创建</el-button>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" ref="table">
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column prop="title" label="分类标题" width="200"/>
			<el-table-column align="center" prop="type" label="类型/数量" width="150">
				<template #default="{ row }">
					<el-tag :type="row.type == 'article' ? '' : 'warning'">{{ type_options[row.type] }}: {{ row.type == 'article' ? row.articles_count : row.topics_count }}</el-tag>
				</template>
			</el-table-column>
			<el-table-column align="center" prop="status" label="状态" width="70">
				<template #default="{ row }">
					<el-tag :type="row.status == 1 ? 'success' : 'danger'">{{ row.status == 1 ? '启用' : "禁用" }}</el-tag>
				</template>
			</el-table-column>
			<el-table-column prop="创建/更新时间" label="创建时间" width="220">
				<template #default="{ row }">
					<view class="mt-0.5 text-gray-700">
						<p>创建：{{ row.create_time || '-' }}</p>
						<p>更新：{{ row.update_time || '-' }}</p>
					</view>
				</template>
			</el-table-column>

			<el-table-column fixed="right" label="操作" width="120">
				<template #default="{ row,$index }">
					<el-button v-permission="'admin.Category/update,POST'" link type="primary" @click="openEdit(row)">编辑</el-button>
					<span v-permission="'admin.Category/delete,POST'">
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

		<!-- 创建/编辑表单 -->
		<FormDrawer ref="formRef" :title="title" :model="form" :rules="rules" @success="handleSubmitSuccess"
			:createApiFun="$api.createCategory" :updateApiFun="$api.updateCategory">
			<el-form-item label="分类标题" prop="title">
				<el-input v-model="form.title" placeholder="分类标题" class="w-[150px]"></el-input>
			</el-form-item>
			<el-form-item label="分类状态" prop="status">
				<el-switch v-model="form.status" :active-value="1" :inactive-value="0"/>
			</el-form-item>
			<el-form-item label="分类类型" prop="type">
				<el-radio-group v-model="form.type">
				  <el-radio label="topic">话题</el-radio>
				  <el-radio label="article">帖子</el-radio>
				</el-radio-group>
			</el-form-item>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: "分类",
				
				type_options:{
					article:"帖子",
					topic:"话题"
				},

				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,

				// 表单
				form: {
					title: "",
					type: "topic",
					status: 1
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
			// 加载数据
			loadData() {
				this.$loading.show()
				this.$api.getCategoryList(this.current_page, {
						limit: 10
					}).then(({
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
				this.$api.deleteCategory(id)
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
			openCreate() {
				this.$refs.formRef.open(this.form)
			},
			// 打开编辑表单
			openEdit(row) {
				this.$refs.formRef.open(this.form, row)
			},
			// 修改/创建成功
			handleSubmitSuccess(editId) {
				// 修改刷新当前页，新增刷新第一页
				if (!editId) {
					this.current_page = 1
				}
				this.loadData()
			}
		}
	}
</script>

<style>

</style>