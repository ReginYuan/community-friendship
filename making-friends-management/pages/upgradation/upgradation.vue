<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.Upgradation/save,POST'">
			<el-button type="primary" @click="openCreate">创建</el-button>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" ref="table">
			<el-table-column prop="id" label="ID" width="60" />
			 <el-table-column prop="appid" label="appid" width="100"/>
			 <el-table-column prop="name" label="应用名称" width="100"/>
			 <el-table-column prop="title" label="升级标题" width="100"/>
			 <el-table-column prop="contents" label="升级内容" width="100"/>
			 <el-table-column prop="platform" label="平台" width="100"/>
			 <!-- <el-table-column align="center" prop="type" label="类型" width="100">
			 	<template #default="{ row }">
			 		<el-tag>{{ row.type }}</el-tag>
			 	</template>
			 </el-table-column> -->
			 <el-table-column prop="version" label="版本号" width="100"/>
			 <el-table-column prop="url" label="下载地址" width="100"/>
			 <el-table-column prop="stable_publish" label="上线发行" width="100">
				 <template #default="{ row }">
				 	<el-tag :type="row.stable_publish == 1 ? 'success' : 'info'">{{ row.stable_publish == 1 ? '上线' : "下线" }}</el-tag>
				 </template>
			</el-table-column>
			 <el-table-column prop="is_mandatory" label="强制更新" width="100">
				<template #default="{ row }">
					<el-tag :type="row.is_mandatory == 1 ? 'success' : 'info'">{{ row.is_mandatory == 1 ? '是' : "否" }}</el-tag>
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
					<el-button v-permission="'admin.Upgradation/update,POST'" link type="primary" @click="openEdit(row)">编辑</el-button>
					<span v-permission="'admin.Upgradation/delete,POST'">
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
			:createApiFun="$api.createUpgradation" :updateApiFun="$api.updateUpgradation">
			<el-form-item label="appid" prop="appid">
				<el-input v-model="form.appid" placeholder="应用appid,例如__UNI__XXXXXX" class="w-[250px]"></el-input>
			</el-form-item>
			<el-form-item label="name" prop="name">
				<el-input v-model="form.name" placeholder="应用名称" class="w-[250px]"></el-input>
			</el-form-item>
			<el-form-item label="升级标题" prop="title">
				<el-input v-model="form.title" placeholder="升级标题" class="w-[300px]"></el-input>
			</el-form-item>
			<el-form-item label="升级版本" prop="version">
				<el-input v-model="form.version" placeholder="升级版本,例如1.0.0" class="w-[150px]"></el-input>
			</el-form-item>
			<el-form-item label="升级内容" prop="contents">
				<el-input :autosize="{ minRows: 2 }"  type="textarea" v-model="form.contents" placeholder="升级内容"></el-input>
			</el-form-item>
			<el-form-item label="平台" prop="platform">
				<el-radio-group v-model="form.platform">
				  <el-radio label="android">安卓</el-radio>
				  <el-radio label="ios">苹果</el-radio>
				</el-radio-group>
			</el-form-item>
			<el-form-item label="安装包" prop="url">
				<el-input v-if="form.platform == 'ios'" v-model="form.url" placeholder="AppStore"></el-input>
				<upload-file v-else v-model="form.url"></upload-file>
			</el-form-item>
			<el-form-item label="上线发行" prop="stable_publish">
				<el-switch v-model="form.stable_publish" :active-value="1" :inactive-value="0"/>
			</el-form-item>
			<el-form-item label="强制更新" prop="is_mandatory">
				<el-switch v-model="form.is_mandatory" :active-value="1" :inactive-value="0"/>
			</el-form-item>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: "升级",
				
				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,

				// 表单
				form: {
					"appid": "",
					"name": "",
					"title": "",
					"contents": "",
					"platform": "android",
					"version": "",
					"url": "",
					"stable_publish": 1,
					"is_mandatory": 0,
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
				this.$api.getUpgradationList(this.current_page).then(({
						data
					}) => {
						this.data = data.data.map(o=>{
							o.platform = o.platform.join(",")
							return o
						})
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
				this.$api.deleteUpgradation(id)
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