import { request } from "./request"
export function getImconversationList(page,keyword = ""){
	let url = `/imconversation/${page}?keyword=${keyword}`
	return request({ url })
}

// 删除消息
export function deleteImconversation(id){
	return request({ 
		url:"/imconversation/delete",
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