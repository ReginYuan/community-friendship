import { request } from "./request"
export function getCategoryList(page, {
	type = "",
	limit = 10
}){
	let url = `/category/${page}?limit=${limit}`
	if(type){
		url += `&type=${type}`
	}
	return request({ url })
}

// 删除分类
export function deleteCategory(id){
	return request({ 
		url:"/category/delete",
		method:"POST",
		data:{
			ids: Array.isArray(id) ? id : [id]
		}
	})
}

// 创建分类
export function createCategory(data){
	return request({ 
		url:"/category/save",
		method:"POST",
		data
	})
}

// 更新分类
export function updateCategory(id,data){
	return request({ 
		url:"/category/update",
		method:"POST",
		data:{
			id,
			...data
		}
	})
}