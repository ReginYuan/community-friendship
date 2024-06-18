import { request } from "./request"
export function getUserActionLogList(page,keyword = ""){
	let url = `/user_action_log/${page}?keyword=${keyword}`
	return request({ url })
}

// 删除日志
export function deleteUserActionLog(id){
	return request({ 
		url:"/user_action_log/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 清空日志
export function clearUserActionLog(){
	return request({ 
		url:"/user_action_log/clear",
		method:"POST"
	})
}