import { request } from "./request"
export function getImmessageList(page,keyword = ""){
	let url = `/immessage/${page}?keyword=${keyword}`
	return request({ url })
}

// 删除消息
export function deleteImmessage(id){
	return request({ 
		url:"/immessage/delete",
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