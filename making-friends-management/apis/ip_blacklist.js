import { request } from "./request"
// 获取ip黑名单列表
export function getIpBlacklist(page,keyword = ""){
	let url = `/ip_blacklist/${page}?keyword=${keyword}`
	return request({ url })
}

// 添加ip黑名单
export function addIpBlacklist(ip){
	return request({ 
		url:"/ip_blacklist/save",
		method:"POST",
		data:{
			ip
		}
	})
}

// 删除ip黑名单
export function deleteIpBlacklist(id){
	return request({ 
		url:"/ip_blacklist/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}
