<template>
	<el-dialog v-model="visible" title="ip黑名单" width="700" top="5vh">
		<search-bar placeholder="ip" @search="submitSearch" />
		<view class="flex pb-2" v-permission="'admin.IpBlacklist/save,POST'">
			<el-button type="primary" @click.stop="addIpBlacklist()">添加ip</el-button>
		</view>
	    <el-table v-loading="loading" :data="data" border stripe style="width: 100%" height="300">
	    	<el-table-column prop="id" label="ID" width="60" />
	    	<el-table-column prop="ip" label="IP地址"/>
	    	<el-table-column prop="create_time" label="加入时间" width="220" />
	    	<el-table-column fixed="right" label="操作" width="200">
	    		<template #default="{ row,$index }">
					<span v-permission="'admin.IpBlacklist/delete,POST'">
						<el-popconfirm :title="'是否要移除该'+title+'?'" @confirm="deleteItem(row.id)">
							<template #reference>
								<el-button link type="danger">移除</el-button>
							</template>
						</el-popconfirm>
					</span>
	    		</template>
	    	</el-table-column>
	    </el-table>
	    <template #footer>
	      <pagination :fixed="false" v-model="current_page" :page-count="last_page" :page-size="per_page" @update:modelValue="loadData()" />
	    </template>
	  </el-dialog>
</template>

<script>
	export default {
		emit:["reload"],
		data() {
			return {
				visible: false,
				title: "ip黑名单",
				data: [],
				total: 0,
				per_page: 10,
				current_page: 1,
				last_page: 1,
				ids: [],
				loading:false,
				keyword:""
			}
		},
		methods: {
			open(){
				this.current_page = 1
				this.loadData()
				this.visible = true
			},
			submitSearch(keyword) {
				this.keyword = keyword
				this.current_page = 1
				this.loadData()
			},
			// 添加ip黑名单
			addIpBlacklist() {
				this.$prompt('请输入拉黑的ip地址，支持127.0.0.*格式', '提示', {
				    confirmButtonText: '添加',
				    cancelButtonText: '取消'
				  })
				.then(({ value }) => {
				    this.loading = true
				    this.$api.addIpBlacklist(value)
				    .then((res) => {
				    	this.$success(res.msg)
				    	// 加载当前页
				    	this.loadData()
						this.$emit("reload")
				    })
				    .finally(() => {
				    	this.loading = false
				    })
				})
			},
			// 加载数据
			loadData() {
				this.loading = true
				this.$api.getIpBlacklist(this.current_page,this.keyword)
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
						this.loading = false
					})
			},
			// 删除/批量删除
			deleteItem(id) {
				if (Array.isArray(id) && id.length == 0) {
					return this.$warning("请先选择要删除的" + this.title)
				}
				this.loading = true
				this.$api.deleteIpBlacklist(id)
					.then((res) => {
						this.$success(res.msg)
						// 加载当前页
						this.loadData()
						this.$emit("reload")
					})
					.finally(() => {
						this.loading = false
					})
			},
		}
	}
</script>

<style>
</style>