import { request } from "./request"
// 登录
export function login(username,password){
	return request({
		url:"/login",
		method:"POST",
		data:{
			username,
			password
		}
	})
}

// 获取用户列表
export function getUserList(page,keyword = ""){
	return request({
		url:`/user/${page}?keyword=${keyword}`
	})
}

// 删除用户
export function deleteUser(id){
	return request({ 
		url:"/user/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 创建用户
export function createUser(data){
	return request({ 
		url:"/user/save",
		method:"POST",
		data
	})
}

// 更新用户
export function updateUser(id,data){
	return request({ 
		url:"/user/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}

// 给用户配置角色
export function setRole(id,data){
	return request({ 
		url:"/user/setrole",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}

// 获取用户信息
export function getUserInfo(){
	return request({
		url:"/user/info",
		method:"GET"
	})
}