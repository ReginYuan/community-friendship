<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.Topic/save,POST'">
			<el-button type="primary" @click="openCreate">创建</el-button>
		</view>
		<!-- 表格 -->
		<el-row>
			<el-col :span="6" class="bg-gray-50">
				<el-affix :offset="44">
					<scroll-view scroll-y="true" style="height: calc(100vh - 44px - 130px);">
						<el-menu :default-active="category_activeId.toString()" class="w-full bg-gray-50" @select="handleSelect">
							<el-menu-item-group title="话题分类" class="py-3">
								<el-menu-item :index="item.id.toString()" v-for="item in categorys" :key="item.id">
									<span>{{ item.title }}</span>
									<el-tag type="info" size="small" class="ml-auto">{{ item.topics_count }}</el-tag>
								</el-menu-item>
							</el-menu-item-group>
						</el-menu>
					</scroll-view>
				</el-affix>
			</el-col>
			<el-col :span="18" v-loading="loading">
				<el-table :data="data" border stripe style="width: 100%" ref="table">
					<el-table-column prop="id" label="ID" width="60" />
					<el-table-column prop="cover" label="话题" width="300">
						
						<template #default="{ row }">
							<uni-list-item :title="row.title" :thumb="row.cover" :note="row.desc"></uni-list-item>
						</template>
					</el-table-column>
					
					<el-table-column align="center" prop="article_count" label="帖子数" width="70"/>
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
							<el-button v-permission="'admin.Topic/update,POST'" link type="primary" @click="openEdit(row)">编辑</el-button>
							<span v-permission="'admin.Topic/delete,POST'">
								<el-popconfirm :title="'是否要删除该'+title+'?'" @confirm="deleteItem(row.id)">
									<template #reference>
										<el-button link type="danger">删除</el-button>
									</template>
								</el-popconfirm>
							</span>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>


		<!-- 分页 -->
		<pagination v-model="current_page" :page-count="last_page" :page-size="per_page"
			@update:modelValue="loadData()" />

		<!-- 创建/编辑表单 -->
		<FormDrawer ref="formRef" :title="title" :model="form" :rules="rules" @success="handleSubmitSuccess"
			:createApiFun="$api.createTopic" :updateApiFun="$api.updateTopic">
			<el-form-item label="话题标题" prop="title">
				<el-input v-model="form.title" placeholder="话题标题" class="w-[150px]"></el-input>
			</el-form-item>
			<el-form-item label="话题封面" prop="cover">
				<view>
					<el-checkbox v-model="manual" label="手动输入"/>
					<upload-image v-if="!manual" :limit="1" :modelValue="form.cover == '' ? [] : [form.cover]" @update:modelValue="onUploadSuccess"></upload-image>
					<view class="flex items-center" v-else>
						<el-image :src="form.cover" style="width: 100px;height: 100px;" class="mr-3"></el-image>
						<el-input v-model="form.cover" placeholder="请输入图片地址"/>
					</view>
				</view>
			</el-form-item>
			<el-form-item label="话题描述" prop="desc">
				<el-input :autosize="{ minRows: 4 }"  type="textarea" v-model="form.desc" placeholder="话题描述"></el-input>
			</el-form-item>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: "话题",

				type_options: {
					article: "帖子",
					topic: "话题"
				},

				categorys:[],
				category_activeId:0,

				loading:false,
				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,

				manual:false,
				// 表单
				form: {
					title: "",
					cover:"",
					desc:"",
					category_id:0
				},
				// 验证规则
				rules: {

				},
			}
		},
		onLoad() {
			this.getCategorys()
		},
		methods: {
			onUploadSuccess(e){
				this.form.cover = e.length == 0 ? "" : e[0]
			},
			// 获取分类列表
			getCategorys(){
				this.$loading.show()
				this.$api.getCategoryList(1,{
					limit:100,
					type:"topic"
				}).then(({data})=>{
					this.categorys = data.data
					if(this.categorys.length > 0 && this.category_activeId == 0){
						this.category_activeId = this.categorys[0].id
					}
					if(this.category_activeId > 0){
						this.loadData()
					}
				})
				.finally(()=>{
					this.$loading.hide()
				})
			},
			handleSelect(id){
				this.category_activeId = Number(id)
				this.current_page = 1
				this.loadData()
			},
			// 加载数据
			loadData() {
				this.loading = true
				this.$api.getTopicList(this.current_page, {
						limit: 10,
						category_id:this.category_activeId
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
						this.loading = false
					})
			},
			// 删除/批量删除
			deleteItem(id) {
				if (Array.isArray(id) && id.length == 0) {
					return this.$warning("请先选择要删除的" + this.title)
				}
				this.$loading.show()
				this.$api.deleteTopic(id)
					.then((res) => {
						this.$refs.table.clearSelection()
						this.$success(res.msg)
						// 加载当前页
						this.getCategorys()
					})
					.finally(() => {
						this.$loading.hide()
					})
			},
			// 打开创建表单
			openCreate() {
				this.$refs.formRef.open(this.form)
				this.form.category_id = this.category_activeId
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
				// this.loadData()
				this.getCategorys()
			}
		}
	}
</script>

<style>
.el-menu-item.is-active {
	background-color: var(--el-menu-hover-bg-color);
}
</style>