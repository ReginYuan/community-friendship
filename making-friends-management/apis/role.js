import { request } from "./request"
export function getRoleList(page){
	let url = `/role/${page}`
	return request({ url })
}

// 删除
export function deleteRole(id){
	return request({ 
		url:"/role/delete",
		method:"POST",
		data:{
			id
		}
	})
}

// 创建
export function createRole(data){
	return request({ 
		url:"/role/save",
		method:"POST",
		data
	})
}

// 更新
export function updateRole(id,data){
	return request({ 
		url:"/role/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}

// 给角色设置权限
export function setRoleRules(data){
	return request({
		url:"/role/setrule",
		method:"POST",
		data
	})
}