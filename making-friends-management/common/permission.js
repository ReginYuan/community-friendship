import { ElNotification } from 'element-plus'
import { noNeedLogin,cachePrefix } from "@/common/config"
import { useUserStore } from "@/stores/user"
// 监听返回、前进url变化
window.addEventListener('popstate', function(event) {  
  checkPermission(window.location.hash.replace("#",""))
});

// 跳转拦截
let events = ["navigateTo","redirectTo","navigateBack"]
events.forEach(e=>{
	uni.addInterceptor(e, {
		invoke(args) {
			console.log(e + " invoke",args);
			return checkPermission(args.url)
		}
	});
})

// 验证页面跳转权限
export function checkPermission(u){
	const userStore = useUserStore()
	let i = noNeedLogin.findIndex(url=>u.startsWith(url))
	if(i == -1){
		// 未登录
		if(!userStore.loginState){
			ElNotification({
				message:"请先登录",
				type: 'warning',
			})
			uni.redirectTo({
				url: '/pages/login/login'
			});
			return false
		}
		return true
	}
	
	return true
}

function hasPermission(value,el = false){
    if(!value){
        throw new Error(`需要配置权限，例如 v-permission="'getStatistics3,GET'"`)
    }
	let rules = uni.getStorageSync(cachePrefix + "rules")
	if(rules){
		rules = Object.keys(rules).map(k => rules[k])
		const hasAuth = rules.includes(value)
		if(el && !hasAuth){
		    el.parentNode && el.parentNode.removeChild(el)
		}
		return hasAuth
	}
	return false
}

export default {
	install(app) {
		app.directive("permission",{
			mounted(el,binding){
				hasPermission(binding.value,el)
			}
		})
	}
}
