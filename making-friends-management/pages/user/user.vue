<template>
	<view class="p-5">
		<search-bar placeholder="用户名/邮箱/手机" @search="submitSearch" />
		<view class="flex py-3" v-permission="'admin.User/save,POST'">
			<el-button type="primary" @click="openCreate">创建</el-button>
		</view>
		<el-table :data="data" border stripe style="width: 100%">
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column prop="username" label="用户" width="250">
				<template #default="{ row }">
					<view class="flex items-start gap-4">
						<el-avatar :src="row.avatar" class="flex-shrink-0" />
						<view>
							<view class="mt-0.5 text-gray-700">
								<p>昵称：{{ row.username || '-' }}</p>
								<p>手机：{{ row.o_phone || '-' }}</p>
								<p>邮箱：{{ row.o_email || '-' }}</p>
								<p>个性签名：{{ row.desc || '-' }}</p>
								<p>角色：{{ row.rolename.join(",") }}</p>
								<p>设置密码：{{ row.password ? '是' : '否' }}</p>
							</view>
						</view>
					</view>
				</template>
			</el-table-column>
			<el-table-column prop="status" label="状态" width="70">
				<template #default="{ row }">
					<el-tag size="small"
						:type="row.status == 1 ? 'success' : 'danger'">{{ row.status == 1 ? '启用' : '禁用' }}</el-tag>
				</template>
			</el-table-column>
			<el-table-column align="center" prop="fans_count" label="粉丝数" width="70" />
			<el-table-column align="center" prop="follows_count" label="关注数" width="70" />
			<el-table-column align="center" prop="articles_count" label="发帖数" width="70" />
			<el-table-column align="center" prop="comments_count" label="评论数" width="70" />
			<el-table-column prop="创建/更新时间" label="创建时间" width="220">
				<template #default="{ row }">
					<view class="mt-0.5 text-gray-700">
						<p>创建：{{ row.create_time }}</p>
						<p>更新：{{ row.update_time }}</p>
					</view>
				</template>
			</el-table-column>

			<el-table-column fixed="right" label="操作" width="180">
				<template #default="{ row,$index }">
					<el-button v-permission="'admin.User/setRole,POST'" link type="primary" @click="openSetRole(row)">设置角色</el-button>
					<el-button v-permission="'admin.User/update,POST'" link type="primary" @click="openEdit(row)">编辑</el-button>
					<span v-permission="'admin.User/delete,POST'">
						<el-popconfirm :title="'是否要删除该'+title+'?'" @confirm="deleteItem(row.id)">
							<template #reference>
								<el-button link type="danger">删除</el-button>
							</template>
						</el-popconfirm>
					</span>
				</template>
			</el-table-column>
		</el-table>

		<pagination v-model="current_page" :page-count="last_page" :page-size="per_page"
			@update:modelValue="loadData()" />

		<!-- 创建/编辑表单 -->
		<FormDrawer ref="formRef" :title="title" :model="form" :rules="rules" @success="handleSubmitSuccess"
			:createApiFun="$api.createUser" :updateApiFun="$api.updateUser">
			<el-form-item label="用户名" prop="username">
				<el-input v-model="form.username" placeholder="用户名" class="w-[300px]"></el-input>
			</el-form-item>
			<el-form-item label="头像" prop="avatar">
				<upload-image :limit="1" :modelValue="form.avatar == '' ? [] : [form.avatar]" @update:modelValue="onUploadSuccess"></upload-image>
			</el-form-item>
			<el-form-item label="密码" prop="password">
				<el-input v-model="form.password" placeholder="密码" class="w-[300px]"></el-input>
			</el-form-item>
			<el-form-item label="手机号" prop="phone">
				<el-input v-model="form.phone" placeholder="手机号" class="w-[300px]"></el-input>
			</el-form-item>
			<el-form-item label="邮箱" prop="email">
				<el-input v-model="form.email" placeholder="邮箱" class="w-[300px]"></el-input>
			</el-form-item>
			<el-form-item label="状态" prop="status">
				<el-switch v-model="form.status" :active-value="1" :inactive-value="0"/>
			</el-form-item>
			<el-form-item label="个性签名" prop="desc">
				<el-input :autosize="{ minRows: 4 }" type="textarea" v-model="form.desc" placeholder="个性签名"></el-input>
			</el-form-item>
		</FormDrawer>

		<FormDrawer ref="roleRef" isDefaultForm title="设置角色" :model="roleForm" @success="handleRoleFormSubmitSuccess" :updateApiFun="$api.setRole">
			<el-form-item label="角色" prop="role_ids">
				<el-checkbox-group v-model="roleForm.role_ids">
				    <el-checkbox v-for="item in roles" :key="item.id" :label="item.id">{{ item.name }}</el-checkbox>
				  </el-checkbox-group>
			</el-form-item>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: "用户",

				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,

				// 所有角色
				roles:[],

				// 表单
				form: {
					"username": "",
					"avatar": "",
					"password": "",
					"phone": "",
					"email": "",
					"status": 1,
					"desc": ""
				},
				// 验证规则
				rules: {

				},
				
				// 角色表单
				roleForm: {
					role_ids:[],
					id:0
				},
			}
		},
		onLoad() {
			this.loadData()
			this.getRoles()
		},
		methods: {
			// 获取角色列表
			getRoles(){
				this.$api.getRoleList(1,{
					limit:100
				}).then(({data})=>{
					this.roles = data.data
				})
			},
			openSetRole(row) {
				this.$refs.roleRef.open(this.roleForm,{
					role_ids:row.roles.map(o=>o.id),
					id:row.id
				})
			},
			handleRoleFormSubmitSuccess(){
				this.loadData()
			},
			// 上传成功
			onUploadSuccess(e){
				this.form.avatar = e.length == 0 ? "" : e[0]
			},
			submitSearch(keyword) {
				this.page = 1
				this.loadData(keyword)
			},
			loadData(keyword = "") {
				this.$loading.show()
				this.$api.getUserList(this.current_page, keyword)
					.then(({
						data
					}) => {
						this.data.length = 0
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
				this.$api.deleteUser(id)
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
				this.form.password = ""
				this.form.phone = row.o_phone
				this.form.email = row.o_email
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