# 约课小程序后台接口

### 说明
下列所有接口的访问前缀为 https://api.chaojinengliang.com/Home/Index/  
数据返回格式为json  
数据库图片存放格式为 /Public/Uploads/2018-08-29/5895f417cca84.jpg  
访问图片需加前缀 https://api.chaojinengliang.com  
注意: 前缀最后没有 /

## 接口

<style>
table th:nth-of-type(2) {
    width: 10%;
}
</style>

| # | Name | Interface | Method | Send | Return |  
|---| ---  | --- | --- | ---| --- |
| 1 | 用户首次登录 | addUser | POST | openid <br> username (微信用户名) | 1: 成功 <br> 0: 失败|
|2 | 获取场馆列表  | getVenueList | GET | | id (场馆id) <br> name (场馆名字) <br> address <br> address_detail <br> info (简介) <br> openid (创建者id) |

