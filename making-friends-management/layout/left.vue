<template>
	<el-menu :default-active="active" class="w-[200px] left" @select="handleSelect">
		<el-menu-item :index="item.page" v-for="(item,index) in menus" :key="index">
		  <el-icon>
			  <component :is="item.icon"/>
		  </el-icon>
		  <span>{{ item.name }}</span>
		</el-menu-item>
	  </el-menu>
</template>

<script>
	import {
		mapState,
		mapStores,
	} from 'pinia'
	import { useUserStore } from "@/stores/user.js"
	export default {
		data() {
			return {
				active: "2"
			}
		},
		computed: {
			...mapStores(useUserStore),
			...mapState(useUserStore, ['menus']),
		},
		created(){
			// this.menus = [{
			// 	icon:"Aim",
			// 	name:"广告位管理",
			// 	page:"/pages/adsense/adsense"
			// },{
			// 	icon:"Document",
			// 	name:"帖子管理",
			// 	page:"/pages/article/article"
			// },{
			// 	icon:"Operation",
			// 	name:"分类管理",
			// 	page:"/pages/category/category"
			// },{
			// 	icon:"MessageBox",
			// 	name:"反馈管理",
			// 	page:"/pages/feedback/feedback"
			// },{
			// 	icon:"Position",
			// 	name:"聊天会话管理",
			// 	page:"/pages/imconversation/imconversation"
			// },{
			// 	icon:"Message",
			// 	name:"聊天信息管理",
			// 	page:"/pages/immessage/immessage"
			// },{
			// 	icon:"DataBoard",
			// 	name:"举报管理",
			// 	page:"/pages/report/report"
			// },{
			// 	icon:"ChatLineSquare",
			// 	name:"话题管理",
			// 	page:"/pages/topic/topic"
			// },{
			// 	icon:"Upload",
			// 	name:"升级中心",
			// 	page:"/pages/upgradation/upgradation"
			// },{
			// 	icon:"setting",
			// 	name:"用户管理",
			// 	page:"/pages/user/user"
			// },{
			// 	icon:"setting",
			// 	name:"日志管理",
			// 	page:"/pages/user_action_log/user_action_log"
			// },{
			// 	icon:"/pages/setting",
			// 	name:"图片审核",
			// 	page:"/pages/ip_image/ip_image"
			// },{
			// 	icon:"setting",
			// 	name:"角色管理",
			// 	page:"/pages/role/role"
			// },{
			// 	icon:"setting",
			// 	name:"权限菜单管理",
			// 	page:"/pages/rule/rule"
			// }]
		},
		methods: {
			handleSelect(url) {
				console.log(url)
				uni.navigateTo({
					url
				});
			}
		},
	}
</script>

<style>
	.left {
		height: calc(100vh - 44px);
	}
	
</style>