import { request } from "./request"
export function getFeedbackList(page){
	let url = `/feedback/${page}`
	return request({ url })
}

// 删除反馈
export function deleteFeedback(id){
	return request({ 
		url:"/feedback/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}
