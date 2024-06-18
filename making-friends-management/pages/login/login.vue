<template>
	<view class="mx-auto max-w-screen-xl px-4 py-16 sm:px-6 lg:px-8">
	  <view class="mx-auto max-w-lg">
	    <h1 class="text-center text-2xl font-bold sm:text-3xl">济南程序员社区</h1>
	
	    <p class="mx-auto mt-4 max-w-md text-center text-gray-500">
	      此站点是“济南程序员社区交友课程” 的演示后台管理
	    </p>
	
	    <view class="mb-0 mt-6 space-y-4 rounded-lg p-4 shadow-lg sm:p-6 lg:p-8">
	      <p class="text-center text-lg font-medium">登录到您的帐户</p>
	
	      <view>
	        <label for="email" class="sr-only">邮箱/手机号</label>
	
	        <view class="relative">
	          <input
	            class="rounded-lg border-gray-200 p-4 pe-12 text-sm shadow-sm"
	            placeholder="邮箱/手机号错误"
				v-model="username"
	          />
	
	          <span class="absolute inset-y-0 end-0 grid place-content-center px-4">
	            <svg
	              xmlns="http://www.w3.org/2000/svg"
	              class="h-4 w-4 text-gray-400"
	              fill="none"
	              viewBox="0 0 24 24"
	              stroke="currentColor"
	            >
	              <path
	                stroke-linecap="round"
	                stroke-linejoin="round"
	                stroke-width="2"
	                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
	              />
	            </svg>
	          </span>
	        </view>
	      </view>
	
	      <view>
	        <label for="password" class="sr-only">密码</label>
	
	        <view class="relative">
	          <input
	            type="password"
	            class="rounded-lg border-gray-200 p-4 text-sm shadow-sm"
	            placeholder="密码"
				v-model="password"
	          />
	        </view>
	      </view>
	
	      <el-button 
			type="primary"
	        class="w-full rounded-lg border-0"
			size="large"
			:disabled="disabled"
			@click="login"
			:loading="loading"
	      >
	        登 录
	      </el-button>
	    </view>
	  </view>
	</view>
</template>

<script>
	import { useUserStore } from "@/stores/user"
	export default {
		data() {
			return {
				username:"",
				password:"",
				loading:false
			}
		},
		computed: {
			disabled() {
				return !this.username.trim() || !this.password.trim()
			}
		},
		methods: {
			login(){
				this.loading = true
				this.$api.login(this.username,this.password)
				.then(res=>{
					const userStore = useUserStore()
					userStore.login(res.data)
					this.$success('登录成功')
					uni.navigateTo({ url:"/" })
				})
				.finally(()=>{
					this.loading = false
				})
			}
		}
	}
</script>

<style>

</style>
