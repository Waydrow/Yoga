# 约课小程序后台接口

### 说明
下列所有接口的访问前缀为 https://api.chaojinengliang.com/Home/Index/  
数据返回格式为json  
数据库图片存放格式为 /Public/Uploads/2018-08-29/5895f417cca84.jpg  
访问图片需加前缀 https://api.chaojinengliang.com  
注意: 前缀最后没有 /

## 接口

| # | Name | Interface | Method | Send | Return |  
|---| ---  | --- | --- | ---| --- |
| 1 | 用户首次登录 | addUser | POST | openid <br> username (微信用户名) | 1: 成功 <br> 0: 失败|
| 2 | 获取场馆列表  | getVenueList | GET | | id (场馆id) <br> name (场馆名字) <br> address <br> address_detail <br> info (简介) <br> openid (创建者id) |
| 3 | 按id查找场馆  | getVenueById | POST | id | 单条场馆信息 |
| 4 | 按name查找场馆 <br>（可能不止一个） | getVenueByName | POST | name | 场馆信息|
| 5 | 场馆入驻 | addOneVenue | POST  | name<br>address<br>address_detail<br>info<br>openid<br>注意：上传的封面name为banner[ ]，图片name为photo[ ] | id：返回场馆id <br> 0：失败|
| 6 | 学员申请绑定场馆 | stuApplyForVenue | POST | name：学员名字 <br> phone <br> openid <br> vid：场馆id <br> flag：（0->学员发起申请，1->场馆发起申请）| id：申请记录id <br> 0：失败 |
| 7 | 处理学员绑定场馆 | dealStuApplyForVenue | POST | id：申请记录id <br> openid <br> vid：场馆id <br> state：（1->通过，2->拒绝） | 1：成功 <br> 0：失败 |
| 8 | 获取消息列表<br>（学员申请绑定场馆）| getStuApplyList | POST | vid：场馆id | id<br> vid：场馆id <br> openid <br> state：(0->待处理，1->通过，2->拒绝) <br> flag：(0->学员发起申请，1->场馆发起申请) <br> date：日期 <br> time：时间 <br> event：事件内容 |
| 9 | 日志列表 | getLogList | POST | vid：场馆id | id <br> vid <br> date <br> time <br> event|
