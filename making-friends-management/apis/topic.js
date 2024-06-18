import { request } from "./request"
export function getTopicList(page,{
	limit = 10,
	category_id = null
}){
	let url = `/topic/${page}?limit=${limit}`
	if(category_id){
		url += `&category_id=${category_id}`
	}
	return request({ url })
}

// 删除话题
export function deleteTopic(id){
	return request({ 
		url:"/topic/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 创建话题
export function createTopic(data){
	return request({ 
		url:"/topic/save",
		method:"POST",
		data
	})
}

// 更新话题
export function updateTopic(id,data){
	return request({ 
		url:"/topic/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}