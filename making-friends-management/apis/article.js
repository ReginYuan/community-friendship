import { request } from "./request"
export function getArticleList(page,keyword = ""){
	let url = `/article/${page}`
	if(keyword){
		url += `?keyword=${keyword}`
	}
	return request({ url })
}

// 删除帖子
export function deleteArticle(id){
	return request({ 
		url:"/article/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 创建帖子
export function createArticle(data){
	return request({ 
		url:"/article/save",
		method:"POST",
		data
	})
}

// 更新帖子
export function updateArticle(id,data){
	return request({ 
		url:"/article/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}