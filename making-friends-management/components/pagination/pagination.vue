<template>
	<view v-if="fixed" class="h-[50px]"></view>
	<view :class="{ 'fixed left-[200px] bottom-0 right-0 z-[10]':fixed }" class="bg-white flex justify-center h-[50px] items-center" style="border-top:1px solid #eeeeee;">
		<el-pagination size="large" layout="prev, pager, next" :page-count="pageCount" :current-page="modelValue" :page-size="pageSize" @current-change="change"/>
	</view>	
</template>

<script>
	export default {
		emits:["update:modelValue"],
		name:"pagination",
		props:{
			pageSize:Number,
			modelValue:Number,
			pageCount:Number,
			fixed:{
				type:Boolean,
				default:true
			}
		},
		methods: {
			change(p) {
				this.$emit("update:modelValue",p)
			}
		},
	}
</script>

<style>

</style>