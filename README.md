# API_Random_Pictures
通过读取json返回随机图片的PHPAPI



 ### 请求参数
|参数|类型|必填|描述|
|--|--|--|--|
|tag       |文本|否|根据tag返回图片 多个tag用英文逗号分割|
|match_all |布尔|否|传递true全部tag满足才返回 传递false一个tag就返回 不填false|
|r18       |布尔|否|传递true百分百返回涩图 传递false百分百返回全年龄图片 不填随机|
|ratio     |整数|否|传递1横屏 2为竖排 3为方形|
|returnType|整数|否|传递1返回字节图片 2返回图片pixiv的url 3返回json信息 默认1|

### 示例请求
```
https://api.xiaomiao-ica.top/random_picture/?r18=false&ratio=1&match_all=false
```

  



