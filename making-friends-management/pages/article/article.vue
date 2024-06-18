<template>
	<view class="p-5">
		<!-- 搜索 -->
		<search-bar placeholder="关键词" @search="submitSearch" />
		<!-- 操作按钮 -->
		<view class="flex py-3">
			<el-button v-permission="'admin.Article/save,POST'" type="primary" @click="openCreate">创建</el-button>
			<span v-permission="'admin.Article/delete,POST'" class="ml-1">
				<el-popconfirm :title="'是否要批量删除选中'+title+'?'" @confirm="deleteItem(ids)">
					<template #reference>
						<el-button type="danger">批量删除</el-button>
					</template>
				</el-popconfirm>
			</span>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" @selection-change="handleSelectionChange" ref="table">
			<el-table-column type="selection" width="55" />
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column prop="username" label="用户" width="350">
				<template #default="{ row }">
					<article-list-item :item="row"></article-list-item>
				</template>
			</el-table-column>
			<el-table-column align="center" prop="ding_count" label="顶数" width="70" />
			<el-table-column align="center" prop="cai_count" label="踩数" width="70" />
			<el-table-column align="center" prop="collect_count" label="收藏数" width="70" />
			<el-table-column align="center" prop="comment_count" label="评论数" width="70" />
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
					<el-button v-permission="'admin.Article/update,POST'" link type="primary" @click="openEdit(row)">编辑</el-button>
					<span v-permission="'admin.Article/delete,POST'">
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
		<FormDrawer ref="formRef" :title="title" :model="form" :rules="rules" @success="handleSubmitSuccess" :createApiFun="$api.createArticle" :updateApiFun="$api.updateArticle">
			<el-form-item label="帖子内容" prop="content">
				<el-input :autosize="{ minRows: 4 }"  type="textarea" v-model="form.content" placeholder="帖子内容"></el-input>
			</el-form-item>
			<el-form-item label="帖子图片" prop="images">
				<view>
					<el-checkbox v-model="manual" label="手动输入"/>
					<view v-if="!manual">
						<upload-image v-model="form.images"/>
					</view>
					<input-group v-else v-model="form.images"></input-group>
				</view>
			</el-form-item>
			<el-form-item label="所属分类" prop="category_id">
				<el-select v-model="form.category_id" placeholder="选择分类" style="width: 240px">
				    <el-option
				      v-for="item in categorys"
				      :key="item.id"
				      :label="item.title"
				      :value="item.id"
				    ></el-option>
				</el-select>
			</el-form-item>
			<el-form-item label="所属话题" prop="topic_id">
				<el-select v-model="form.topic_id" placeholder="选择话题" style="width: 240px">
				    <el-option
				      v-for="item in topics"
				      :key="item.id"
				      :label="item.title"
				      :value="item.id"
					  class="flex items-center"
				    >
					  <el-tag size="small" class="mr-2" v-if="item.category_name">{{ item.category_name }}</el-tag>
					  <el-image :src="item.cover" style="width: 20px; height: 20px;" class="mr-2"></el-image>
				      <span>{{ item.title }}</span>
				    </el-option>
				</el-select>
			</el-form-item>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title:"帖子",
				
				keyword:"",
				
				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,
				ids: [],
				
				// 选项
				article_categorys:[],
				topic_categorys:[],
				topics:[],
				// 表单
				manual:false,
				form: {
					content: "",
					category_id:null,
					images:[],
					topic_id:null
				},
				// 验证规则
				rules: {

				},
			}
		},
		onLoad() {
			this.loadData()
			this.getCategorys("article")
			this.getTopics()
		},
		methods: {
			// 获取分类列表
			getCategorys(type){
				this.$api.getCategoryList(1,{
					limit:100,
					type
				}).then(({data})=>{
					let d = [
						{
							id:0,
							title:"未选择"
						},
						...data.data
					]
					this.categorys = d
				})
			},
			// 获取话题列表
			getTopics(){
				this.$api.getTopicList(1,{
					limit:100
				}).then(({data})=>{
					this.topics = [
						{
							id:0,
							title:"未选择"
						},
						...data.data
					]
				})
			},
			// 多选
			handleSelectionChange(e) {
				this.ids = e.map(o => o.id)
			},
			// 搜索
			submitSearch(keyword) {
				this.page = 1
				this.keyword = keyword
				this.loadData()
			},
			// 加载数据
			loadData() {
				this.$loading.show()
				this.$api.getArticleList(this.current_page, this.keyword)
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
				this.$api.deleteArticle(id)
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