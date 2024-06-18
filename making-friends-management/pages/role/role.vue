<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.Role/save,POST'">
			<el-button type="primary" @click="openCreate">创建</el-button>
		</view>
		<!-- 表格 -->
		<el-table :data="data" border stripe style="width: 100%" ref="table">
			<el-table-column prop="id" label="ID" width="60" />
			<el-table-column prop="name" label="角色名称" width="150" />
			<el-table-column prop="desc" label="角色描述" />
			<el-table-column prop="创建/更新时间" label="创建时间" width="220">
				<template #default="{ row }">
					<view class="mt-0.5 text-gray-700">
						<p>创建：{{ row.create_time || '-' }}</p>
						<p>更新：{{ row.update_time || '-' }}</p>
					</view>
				</template>
			</el-table-column>
			<el-table-column fixed="right" label="操作" width="180">
				<template #default="{ row,$index }">
					<el-button v-permission="'admin.Role/setRule,POST'" link type="primary" @click="openSetRule(row)">配置权限</el-button>
					<el-button v-permission="'admin.Role/update,POST'" link type="primary" @click="openEdit(row)">编辑</el-button>
					<span v-permission="'admin.Role/delete,POST'">
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
			:createApiFun="$api.createRole" :updateApiFun="$api.updateRole">
			<el-form-item label="角色名称" prop="name">
				<el-input v-model="form.name" placeholder="角色名称" class="w-[250px]"></el-input>
			</el-form-item>
			<el-form-item label="角色描述" prop="desc">
				<el-input v-model="form.desc" placeholder="角色描述" class="w-[250px]"></el-input>
			</el-form-item>
		</FormDrawer>

		<!-- 权限配置 -->
		<FormDrawer ref="setRuleFormDrawerRef" title="权限配置" @submit="handleSetRuleSubmit">
			<el-tree-v2 ref="elTreeRef" node-key="id" :check-strictly="checkStrictly"
				:default-expanded-keys="defaultExpandedKeys" :data="ruleList"
				:props="{ label:'name',children:'children' }" show-checkbox :height="treeHeight"
				@check="handleTreeCheck">
				<template #default="{ node,data }">
					<div class="flex items-center">
						<el-icon v-if="data.icon && data.ismenu" class="ml-1">
							<component :is="data.icon" />
						</el-icon>
						<span v-if="!data.ismenu" class="bg-gray-2 ml-1 w-[30px] text-center"
							style="font-size: 10px;padding: 1px 0;border-radius: 2px;">{{ data.method }}</span>
						<span class="ml-1"> {{ data.name }} </span>
					</div>
				</template>
			</el-tree-v2>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: "角色管理",

				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,

				// 表单
				form: {
					name: "",
					desc: "",
				},
				// 验证规则
				rules: {

				},

				// 设置权限表单
				setRuleForm: {
					role_id: 0,
					rule_ids: []
				},
				treeHeight: 0,
				checkStrictly: false,
				ruleList: [],
				defaultExpandedKeys: []
			}
		},
		onLoad() {
			this.loadData()
		},
		computed: {
			urls() {
				return this.data.map(o => o.src)
			}
		},
		methods: {
			// 打开设置权限弹框
			openSetRule(row) {
				this.treeHeight = window.innerHeight - 180
				this.checkStrictly = true

				this.$api.getRuleList(1).then(res => {
					this.ruleList = res.data.data
					this.defaultExpandedKeys = res.data.data.map(o => o.id)
					this.$refs["setRuleFormDrawerRef"].open()

					this.setRuleForm.role_id = row.id
					// 当前角色拥有的权限ID
					this.setRuleForm.rule_ids = row.roleRules.map(o => o.rule_id)
					setTimeout(() => {
						this.$refs["elTreeRef"].setCheckedKeys(this.setRuleForm.rule_ids)
						this.checkStrictly = false
					}, 150);
				})
			},
			handleSetRuleSubmit() {
				this.$refs["setRuleFormDrawerRef"].showLoading()
				this.$api.setRoleRules(this.setRuleForm)
					.then(res => {
						this.$success("配置成功")
						this.loadData()
						this.$refs["setRuleFormDrawerRef"].close()
					})
					.finally(() => {
						this.$refs["setRuleFormDrawerRef"].hideLoading()
					})
			},
			handleTreeCheck(...e) {
				const {
					checkedKeys,
					halfCheckedKeys
				} = e[1]
				this.setRuleForm.rule_ids = [...checkedKeys, ...halfCheckedKeys]
			},
			// 加载数据
			loadData() {
				this.$loading.show()
				this.$api.getRoleList(this.current_page, {
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
				this.$api.deleteRole(id)
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