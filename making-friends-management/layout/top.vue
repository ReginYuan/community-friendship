<template>
	<view class="h-[44px]">
		<view class="flex h-[42px] items-center px-3" style="border-bottom: 1px solid #eeeeee;">
			<navigator class="font-bold" style="font-size: 18px;" url="/">
				帝莎编程
			</navigator>
			<view class="flex ml-auto">
				<el-avatar :src="avatar" size="small"></el-avatar>
				<el-text type="primary" size="small" class="mx-2">{{name}}</el-text>
				<el-button size="small" text @click="logout">退出登录</el-button>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		mapState,
		mapStores,
	} from 'pinia'
	import { useUserStore } from "@/stores/user.js"
	export default {
		computed: {
			...mapStores(useUserStore),
			...mapState(useUserStore, ['avatar','name']),
		},
		methods: {
			logout() {
				this.$confirm('是否要退出登录？')
				.then(() => {
					uni.navigateTo({
						url:"/pages/login/login"
					})
					this.userStore.logout()
					this.$success("退出成功")
				})
				.catch(() => {
				  // catch error
				})
			}
		},
	}
</script>

<style>
</style>