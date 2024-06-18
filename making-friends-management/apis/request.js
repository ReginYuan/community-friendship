import { baseURL,cachePrefix } from "@/common/config"
import { ElNotification } from 'element-plus'
import * as user from "@/apis/user"
import * as article from "@/apis/article"
import * as category from "@/apis/category"
import * as topic from "@/apis/topic"
import * as adsense from "@/apis/adsense"
import * as report from "@/apis/report"
import * as feedback from "@/apis/feedback"
import * as upgradation from "@/apis/upgradation"
import * as role from "@/apis/role"
import * as rule from "@/apis/rule"
import * as user_action_log from "@/apis/user_action_log"
import * as ip_blacklist from "@/apis/ip_blacklist"
import * as ip_image from "@/apis/ip_image"
import * as immessage from "@/apis/immessage"
import * as imconversation from "@/apis/imconversation"
export default {
	install(app) {
		app.config.globalProperties.$api = {
			...user,
			...article,
			...category,
			...topic,
			...adsense,
			...report,
			...feedback,
			...upgradation,
			...role,
			...rule,
			...user_action_log,
			...ip_blacklist,
			...ip_image,
			...immessage,
			...imconversation
		}
	}
}

// 请求拦截器
export function request({
	url,
	method = "GET",
	data = {}
}){
	return new Promise((resolve,reject)=>{
		// 请求前
		let header = {}
		const token = uni.getStorageSync(cachePrefix + "token")
		if(token){
			header.token = token
		}
		uni.request({
			url:baseURL + url,
			method,
			data,
			header,
			success:(args)=>{
				// 失败提示
				if(args.data.code == 0){
					ElNotification({
						message:args.data.msg,
						type: 'error',
					})
					return reject(args.data)
				}
				return resolve(args.data)
			},
			fail: (err) => {
				console.log('request-fail', err)
				ElNotification({
					message:err.errMsg,
					type: 'error',
				})
				reject(err)
			}
		})
	})
}