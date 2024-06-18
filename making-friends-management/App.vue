<script>
	import { useUserStore } from "@/stores/user"
	import { checkPermission } from "@/common/permission.js"
	export default {
		async onLaunch() {
			// 初始化用户信息
			const userStore = useUserStore()
			await userStore.initUser()
			
			// 获取当前页面url(页面刷新时)
			checkPermission(window.location.hash.replace("#",""))
			
			console.log('App Launch')
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style>
	/*每个页面公共css */
	.uni-left-window {
		height: calc(100vh - 44px);
		overflow-y: auto;
		overflow-x: hidden;
	}
	uni-main {
		overflow-y: auto;
	}
	.el-pagination .btn-prev ,.el-pagination>.is-last{
	    background: transparent;
	    border: 0;
		cursor: pointer;
	}
	.el-pagination>.is-last:hover {
		color: var(--el-pagination-hover-color);
	}
</style>
