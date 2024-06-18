<template>
	<view class="p-5">
		<!-- 操作按钮 -->
		<view class="flex py-3" v-permission="'admin.Rule/save,POST'">
			<el-button type="primary" @click="openCreate(0)">创建</el-button>
		</view>
		<!-- 表格 -->
		<el-table default-expand-all row-key="id" :data="data" border style="width: 100%" ref="table" :row-class-name="rowClassName">
			<el-table-column prop="id" label="ID" width="80" />
			<el-table-column prop="name" label="权限名称" width="200">
				<template #default="{ row }">
					<view class="flex items-center">
						<el-icon v-if="row.icon && row.ismenu">
						   <component :is="row.icon"/>
						</el-icon>
						<el-tag v-if="!row.ismenu" size="small" class="w-[45px]">{{ row.method }}</el-tag>
						<text class="ml-1">{{ row.name }}</text>
					</view>
				</template>
			</el-table-column>
			<el-table-column prop="status" label="状态" width="80">
				<template #default="{ row }">
					<el-tag size="small" :type="status_color[row.status]">{{ status_text[row.status] }}</el-tag>
				</template>
			</el-table-column>
			<el-table-column prop="condition" label="地址" width="300"/>
			<el-table-column prop="order" label="排序" width="60"/>
			<el-table-column prop="create_time" label="创建时间" width="220"/>
			<el-table-column prop="update_time" label="更新时间" width="220"/>
			<el-table-column fixed="right" label="操作" width="180">
				<template #default="{ row,$index }">
					<el-button v-permission="'admin.Role/save,POST'" link type="primary" @click="openCreate(row.id)">添加</el-button>
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
		<pagination v-model="current_page" :page-count="last_page" :page-size="per_page" @update:modelValue="loadData()" />

		<!-- 创建/编辑表单 -->
		<FormDrawer ref="formRef" :title="title" :model="form" :rules="rules" @success="handleSubmitSuccess" :createApiFun="$api.createRule" :updateApiFun="$api.updateRule">
			<el-form-item label="选择类型" prop="ismenu">
				<el-radio-group v-model="form.ismenu">
					<el-radio :label="1" border>菜单</el-radio>
					<el-radio :label="0" border>权限</el-radio>
				</el-radio-group>
			</el-form-item>
			<el-form-item :label="t + '名称'" prop="name">
				<el-input v-model="form.name" :placeholder="t + '名称'" class="w-[250px]"></el-input>
			</el-form-item>
			<el-form-item v-if="!form.ismenu" label="父级菜单" prop="rule_id">
				<el-cascader v-model="form.rule_id"
				:options="options" 
				:props="{value:'id', label:'name',children:'child',checkStrictly:true,emitPath:false }" 
				placeholder="请选择上级菜单"/>
			</el-form-item>
			<el-form-item label="状态" prop="status">
				<el-switch v-model="form.status" :active-value="1" :inactive-value="0"/>
			</el-form-item>
			<el-form-item label="排序" prop="order">
				<el-input-number v-model="form.order" :min="0" :max="1000" />
			</el-form-item>
			<el-form-item v-if="form.ismenu" label="图标" prop="icon">
				<icon-select v-model="form.icon"/>
			</el-form-item>
			<el-form-item v-if="!form.ismenu" label="请求类型" prop="method">
				<el-select v-model="form.method" placeholder="请选择请求类型" class="w-[100px]">
					<el-option
					v-for="item in ['GET','POST','PUT','DELETE']"
					:key="item"
					:label="item"
					:value="item"
					/>
				</el-select>
			</el-form-item>
			<el-form-item :label="t + '地址'" prop="condition">
				<el-input v-model="form.condition" :placeholder="t + '地址'" class="w-[400px]"></el-input>
			</el-form-item>
		</FormDrawer>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: "权限菜单管理",

				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,

				// 表单
				form: {
					name: "",
					rule_id:0,
					status:1,
					order:50,
					ismenu:0,
					icon:"",
					method:"GET",
					condition:""
				},
				// 验证规则
				rules: {

				},
				status_color:{
					0:"danger",
					1:"success",
				},
				status_text:{
					0:"禁用",
					1:"启用",
				},
				
				rowClassName:function(e){
					return e.row.ismenu ? "!bg-gray-1" : ""
				}
			}
		},
		onLoad() {
			this.loadData()
		},
		computed: {
			options(){
				let os = []
				this.data.forEach(o=>{
					if(o.ismenu){
						os.push({
							id:o.id,
							name:o.name,
							child:[]
						})
					}
				})
				return os
			},
			urls() {
				return this.data.map(o=>o.src)
			},
			t(){
				return this.form.ismenu ? "菜单" : "接口"
			}
		},
		methods: {
			// 加载数据
			loadData() {
				this.$loading.show()
				this.$api.getRuleList(this.current_page, {
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
				this.$api.deleteRule(id)
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
			openCreate(rule_id = 0) {
				this.$refs.formRef.open(this.form)
				this.form.rule_id = rule_id
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