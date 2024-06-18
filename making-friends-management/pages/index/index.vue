<template>
	<view class="p-2">
		后台首页
	</view>
</template>

<script>
	export default {
		data() {
			return {

			}
		},
		onLoad(){
			uni.redirectTo({
				url:"/pages/article/article"
			})
		},
		computed: {
			
		},
		methods: {
			
		},
	}
</script>

<style>

</style>