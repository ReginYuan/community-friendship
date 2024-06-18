import { request } from "./request"
export function getUpgradationList(page){
	let url = `/upgradation/${page}`
	return request({ url })
}

// 删除升级
export function deleteUpgradation(id){
	return request({ 
		url:"/upgradation/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 创建升级
export function createUpgradation(data){
	return request({ 
		url:"/upgradation/save",
		method:"POST",
		data
	})
}

// 更新升级
export function updateUpgradation(id,data){
	return request({ 
		url:"/upgradation/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}