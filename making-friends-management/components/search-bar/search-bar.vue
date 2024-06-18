<template>
	<el-form :inline="true" class="search-form">
	    <el-form-item>
	      <el-input v-model="keyword" :placeholder="placeholder" clearable />
	    </el-form-item>
		<slot/>
	    <el-form-item>
	      <el-button type="primary" @click="handleSearch">搜索</el-button>
	    </el-form-item>
	  </el-form>
</template>

<script>
	export default {
		emits:["search"],
		name:"search-bar",
		props: {
			placeholder: {
				type: String,
				default: "关键词"
			},
		},
		data() {
			return {
				keyword:""
			};
		},
		methods:{
			handleSearch(){
				this.$emit("search",this.keyword)
			}
		}
	}
</script>

<style>
.search-form .el-input {
  --el-input-width: 220px;
}

.search-form .el-select {
  --el-select-width: 220px;
}
</style>