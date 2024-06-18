import { defineStore } from 'pinia';
import { cachePrefix } from "@/common/config"
import { getUserInfo } from '../apis/user'
export const useUserStore = defineStore('user', {
	state: () => {
		return { 
			avatar: "",
			name:"",
			token:"",
			menus:[],
			rules:[]
		};
	},
	getters:{
		loginState: (state) => !!state.token
	},
	actions: {
		// 初始化用户信息
		async initUser(){
			let token = uni.getStorageSync(cachePrefix + "token")
			if(token){
				// 获取最新用户信息
				let { data } = await getUserInfo()
				data.token = token
				this.setUser(data)
			}
		},
		// 设置用户信息
		setUser(e) {
			this.token = e.token
			this.name = e.name
			this.avatar = e.avatar
			this.menus = e.menus || []
			this.rules = e.rules || []
			uni.setStorageSync(cachePrefix + "user",e)
			uni.setStorageSync(cachePrefix + "rules",this.rules)
		},
		// 登录
		login(e){
			this.setUser(e)
			uni.setStorageSync(cachePrefix + "token",e.token)
		},
		// 退出登录
		logout(){
			this.token = ""
			this.name = ""
			this.avatar = ""
			this.rules = []
			this.menus = []
			uni.removeStorageSync(cachePrefix + "user")
			uni.removeStorageSync(cachePrefix + "token")
			uni.removeStorageSync(cachePrefix + "rules")
		}
	},
});
