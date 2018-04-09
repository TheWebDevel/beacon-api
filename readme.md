# Beacon API 

## Endpoints

----

### `GET /info`

Get basic version information of API.

`GET /info`

----

### `POST /auth/signup`

New user sign up

`POST /auth/signup`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| user_mobile | Required, digits:10 | 10 Digit valid mobile number of the user|

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| OTP | String | `6 Digit` One time password |

----

### `POST /auth/verify`

Validate entered OTP for successful sign up.

`POST /auth/verify`

**Request Parameters**

| Parameter | Type | Description |
|----|----|----|
| user_mobile | String | 10-digit registered mobile number |
| user_otp | String | 6-digit one-time password received in previous request |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| message | String | Confirmation message |
| token | String | User authentication token for subsequent requests |
| user | Object | `User` object |

----

### `POST /auth/cookie`

Validate stored token for auto-login.

`POST /auth/cookie`

**Request Parameters**

| Parameter | Type | Description |
|----|----|----|
| id | String | `id` parameter from `User` object |
| user_auth_token | String | `token` parameter from previous request |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| token | String | User authentication token for subsequent requests |
| user | Object | `User` object |
| friends | Object | `Friends` object |

----


### `POST /auth/login`

Login registered users (using mobile number and password).

`POST /auth/login`

**Request Parameters**

| Parameter | Type | Description |
|----|----|----|
| user_mobile | digits:10 | 10-digit registered mobile number |
|user_password| String | User password |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| message | String | Confirmation message |
| user | Object | `User` object |
| token | Object | User `token` |
| friends | Object | `Friends` of the user | 
----
### `POST /store_details`

Store the details of the user

`POST /store_details`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| user_password | Required, String| Password of the user account |
| user_fname | Required, String | First name of the user |
| user_lname | Required, String | Last name of the user |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Message | String | Confirmation message |

----

### `POST /store`

Store the friends of the user in friends table by referring user table with contacts

`POST /store`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| user_mobile | Required, String | Mobile numbers as `,` seperated values |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Message | String | Confirmation message |

----

### `GET /index`

Get the friends of the user to display it in the friend list

`GET /index`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| User ID (`Middleware`) | Required, exists:users,id | ID of the user from middleware |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Friends | Object | `Friends` object |

----

### `GET /location/{slug}`

Get the location of a friend

`GET /location/{slug}`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| slug (From `URL`) | Required, exists:users,user_slug| SLUG of the friend |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| friend.location | Object | `Location` object |

----

### `POST /allow_location{slug}`

Allow location for a particular friend

`POST /allow_location/{slug}`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| slug (From `URL`) | Required, exists:users,user_slug | Friend SLUG |
| location_isallowed | required, digits_between:0,1 | Location_isallowed is 0 or 1 informaton |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Message | String | Confirmation Message |

----

### `POST /location`

Store current locaton of the user

`POST /location`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| location | Required, string | Current location of the user |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Message | String | Confirmation Message |

----

### `POST /group_store`

Create a new group and add friends to the group

`POST /group_store`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| user_slug| Required, string | User slug in `,` seperated format |

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Message | String | Confirmation Message |

----

### `Get /group_index/{group_slug}`

Display the users of a group with their corresponding location

`GET /group_index/{group_slug}`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| group_slug| Required, string , exists:group,group_slug| Group Slug|

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| group users | Array | Users of the group |

----

### `POST /add_users/{group_slug}`

Add users to an existing group

`POST /add_users/{group_slug}`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| group_slug | Required, string , exists:group,group_slug| Group Slug|
| user_slug | Required, string | Friend slug in `,` seperated format|

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Message | String | Confirmation message |

----

### `POST /group_location/{group_slug}`

Allow location for a group

`POST /group_location/{group_slug}`

**Request Headers**

| Type | Requirement | Description |
|----|----|----|
| group_slug | Required, string , exists:group,group_slug| Group Slug|
| user_isin | Required, string, digits_betweed:0,1 | Location data (0 or 1)|

**Response Data**

| Parameter | Type | Description |
|----|----|----|
| Message | String | Confirmation message |

----









