# Restore API
Restore is a RESTful service for handling order transaction in general online store.

### Requirements
- `composer`
- `mysql ^5.6`

### Local Installation
- `clone` or download and extract zip from this source to your web root folder.
- run `composer install` to install dependencies.
- configure database in `db.php`.
- execute migration with `vendor/bin/yii migrate --appconfig=config-console.php`.
- enable `prettyUrl` in server configuration as well as point document root to `/web`. You can also run built in server by executing `vendor/bin/yii serve --docroot=./web`. It will run on port `8080` by default.


### API Documentation
For every call, `/v1/` indicates you are accessing api major version 1.0.

#### User Registration

`POST /v1/register`

**Success Scenario**

Request
```javascript
POST /v1/register
{
  name: Admin
  password:123456
  email:admin@timicron.com
}
```

Response
```javascript
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin",
    "email": "admin@timicron.com",
    "created": "2018-01-15 16:59:48",
    "updated": "2018-01-15 16:59:48",
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/profile"
      }
    }
  }
}
```

**Error Scenario**

Request
```javascript
POST /v1/register
```

Response
```javascript
{
  "success": false,
  "data": [{
    "field": "name",
    "message": "Name cannot be blank."
  }, {
    "field": "password",
    "message": "Password cannot be blank."
  }, {
    "field": "email",
    "message": "Email cannot be blank."
  }]
}
```

#### Obtain authorization code
Authorization code valid for 5 minutes.

**Success Scenario**

Request
```javascript
POST /v1/authorize
{
  email: admin@timicron.com
  password: 123456
}
```

Response
```javascript
{
  "success": true,
  "data": {
    "authorization_code": "f74f1c96401be1e39d89fe0323b685a4",
    "expired_at": "2018-01-15 18:00:35"
  }
}
```

**Error Scenario**

Request
```javascript
POST /v1/authorize
```

Response
```javascript
{
  "success": false,
  "data": [{
    "field": "email",
    "message": "Email cannot be blank."
  }, {
    "field": "password",
    "message": "Password cannot be blank."
  }]
}
```
#### Obtain access token
Access token valid for 60 days.

**Success Scenario**

Request
```javascript
POST /v1/accesstoken
{
  "auth": "f74f1c96401be1e39d89fe0323b685a4"
}
```

Response
```javascript
{
  "success": true,
  "data": {
    "access_token": "bc7ad31601acb6bb16d026efa524e33c",
    "expired_at": "2018-03-16 17:59:46"
  }
}
```

**Error Scenario**

Request
```javascript
POST /v1/accesstoken
```

Response
```javascript
{
  "success": false,
  "data": {
    "name": "Not Found",
    "message": "There's no authorization code provided.",
    "code": 0,
    "status": 404
  }
}
```

After access token is obtained, you can then request restricted APIs by providing `access_token` parameter in your call.
```javascript
?access_token=bc7ad31601acb6bb16d026efa524e33c
```
-----------------

Below are api calls which need an access token from registered user.

#### User Profile
```javascript
GET /v1/profile?access_token=bc7ad31601acb6bb16d026efa524e33c
```
Success Response
```javascript
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Admin",
    "email": "admin@timicron.com",
    "created": "2018-01-15 16:59:48",
    "updated": "2018-01-15 18:11:51",
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/profile"
      }
    }
  }
}
```
Error Response
```javascript
{
  "success": false,
  "data": {
    "name": "Unauthorized",
    "message": "Your request was made with invalid credentials.",
    "code": 0,
    "status": 401
  }
}
```

#### Logout

```javascript
GET /v1/logout?access_token=bc7ad31601acb6bb16d026efa524e33c
```

Success Response
```javascript
{
  "success": true,
  "data": "Logged out successfully."
}
```

Error Response
```javascript
{
  "success": false,
  "data": {
    "name": "Unauthorized",
    "message": "Your request was made with invalid credentials.",
    "code": 0,
    "status": 401
  }
}
```

Below are admin areas (prefix : `/v1//admin/`) for CRUD operation on coupon, product, and logistic.<br>
An `access_token` is needed as well.<br>
Their patterns are similar and here is just sample for one CRUD operation (product).<br>
Please change accordingly for other CRUD operations.

#### List all items
```javascript
GET /v1/admin/products?access_token=bc7ad31601acb6bb16d026efa524e33c
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "items": [{
      "id": 1,
      "name": "Product A",
      "description": "",
      "brand": "Brand AA",
      "quantity": 99,
      "created": "2018-01-12 16:54:55",
      "updated": "2018-01-15 05:59:03",
      "_links": {
        "self": {
          "href": "http://localhost:8080/v1/admin/product/view?id=1"
        },
        "edit": {
          "href": "http://localhost:8080/v1/admin/product/view?id=1"
        },
        "index": {
          "href": "http://localhost:8080/admin/products"
        }
      }
    }, {
      "id": 2,
      "name": "B",
      "description": "",
      "brand": "",
      "quantity": 99,
      "created": "2018-01-12 16:54:55",
      "updated": "2018-01-12 16:54:55",
      "_links": {
        "self": {
          "href": "http://localhost:8080/v1/product/admin/view?id=2"
        },
        "edit": {
          "href": "http://localhost:8080/v1/admin/product/view?id=2"
        },
        "index": {
          "href": "http://localhost:8080/admin/products"
        }
      }
    }],
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/admin/products?access_token=bc7ad31601acb6bb16d026efa524e33c&page=1"
      }
    },
    "_meta": {
      "totalCount": 2,
      "pageCount": 1,
      "currentPage": 1,
      "perPage": 20
    }
  }
}
```

As you can see in the response, there are some informations about the total count, page count, etc. <br>
There are also links that allow you to navigate to other pages of data.<br>
For example, `http://localhost:8080/v1/admin/products?access_token=bc7ad31601acb6bb16d026efa524e33c&page=2` <br>
would give you the next page of the user data. <br>
`http://localhost:8080/v1/admin/products?access_token=bc7ad31601acb6bb16d026efa524e33c&sort=id` <br>
will sort collection ascending order by `id`, or give minus sign `&sort=-id` for descending order.

Empty Collection Response
```javascript
{
  "success": true,
  "data": {
    "items": [],
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/admin/products?access_token=bc7ad31601acb6bb16d026efa524e33c&page=1"
      }
    },
    "_meta": {
      "totalCount": 0,
      "pageCount": 0,
      "currentPage": 1,
      "perPage": 20
    }
  }
}
```

#### Item Detail
```javascript
GET /v1/admin/products/1?access_token=bc7ad31601acb6bb16d026efa524e33c
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product A",
    "description": "",
    "brand": "Brand AA",
    "quantity": 99,
    "created": "2018-01-12 16:54:55",
    "updated": "2018-01-15 05:59:03",
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/admin/products/1"
      },
      "edit": {
        "href": "http://localhost:8080/v1/admin/products/1"
      },
      "delete": {
        "href": "http://localhost:8080/v1/admin/product/delete?id=1"
      },
      "index": {
        "href": "http://localhost:8080/admin/products"
      }
    }
  }
}
```
Error Response
```javascript
{
  "success": false,
  "data": {
    "name": "Not Found",
    "message": "Object not found: 3",
    "code": 0,
    "status": 404
  }
}
```

#### Update Item
```javascript
PUT /v1/admin/products/1?access_token=bc7ad31601acb6bb16d026efa524e33c
{
    "name": "Product AA",
    "brand": "Brand AAA",
    "quantity":100,
    "description":"Good product."
}
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product AA",
    "description": "Good product.",
    "brand": "Brand AAA",
    "quantity": 100,
    "created": "2018-01-12 16:54:55",
    "updated": "2018-01-15 05:59:03",
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/admin/products/1"
      },
      "edit": {
        "href": "http://localhost:8080/v1/admin/products/1"
      },
      "delete": {
        "href": "http://localhost:8080/v1/admin/product/delete?id=1"
      },
      "index": {
        "href": "http://localhost:8080/admin/products"
      }
    }
  }
}
```

Validation Error Response
```javascript
{
  "success": false,
  "data": [{
    "field": "name",
    "message": "Name should contain at most 255 characters."
  }, {
    "field": "brand",
    "message": "Brand should contain at most 25 characters."
  }]
}
```

#### Delete item
```javascript
DELETE /v1/admin/products/1?access_token=bc7ad31601acb6bb16d026efa524e33c
```

Success Response
```javascript

```
No content will be shown after successful deletion. Thus, you may get header status code (204) if needed.

Error Response
```javascript
{
  "success": false,
  "data": {
    "name": "Not Found",
    "message": "Failed to delete the object for unknown reason.",
    "code": 0,
    "status": 500
  }
}
```
-------

### Order Transaction

Order transaction divided into two categories:
- guest user or customer who order the transaction
-- viewing product catalogue, order checkout and payment confirmation doesn't need token
-- accessing order history and its shiping details require customer token with similar process as user token except authorization is with customer email
- registered user or admin who proceed the order (always need user token)

#### Customer Catalogue Listing

```javascript
GET /v1/catalogues
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "items": [{
      "id": 1,
      "name": "Product A",
      "description": "",
      "brand": "Brand AA",
      "quantity": 99,
      "created": "2018-01-12 16:54:55",
      "updated": "2018-01-15 05:59:03",
      "_links": {
        "self": {
          "href": "http://localhost:8080/v1/admin/product/view?id=1"
        },
        "edit": {
          "href": "http://localhost:8080/v1/admin/product/view?id=1"
        },
        "index": {
          "href": "http://localhost:8080/admin/products"
        }
      }
    }, {
      "id": 2,
      "name": "B",
      "description": "",
      "brand": "",
      "quantity": 99,
      "created": "2018-01-12 16:54:55",
      "updated": "2018-01-12 16:54:55",
      "_links": {
        "self": {
          "href": "http://localhost:8080/v1/product/admin/view?id=2"
        },
        "edit": {
          "href": "http://localhost:8080/v1/admin/product/view?id=2"
        },
        "index": {
          "href": "http://localhost:8080/admin/products"
        }
      }
    }],
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/catalogues?page=1"
      }
    },
    "_meta": {
      "totalCount": 2,
      "pageCount": 1,
      "currentPage": 1,
      "perPage": 20
    }
  }
}
```

Empty Response
```javascript
{
  "success": true,
  "data": {
    "items": [],
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/catalogues"
      }
    },
    "_meta": {
      "totalCount": 0,
      "pageCount": 0,
      "currentPage": 1,
      "perPage": 20
    }
  }
}
```

### Customer Catalogue Detail
```javascript
GET /v1/catalogues/1
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product A",
    "description": "",
    "brand": "Brand AA",
    "quantity": 99,
    "created": "2018-01-12 16:54:55",
    "updated": "2018-01-15 22:35:54",
    "_links": {
      "self": {
        "href": "http://localhost:8080/v1/admin/products/1"
      },
      "edit": {
        "href": "http://localhost:8080/v1/admin/products/1"
      },
      "delete": {
        "href": "http://localhost:8080/v1/admin/products/1"
      },
      "index": {
        "href": "http://localhost:8080/admin/products"
      }
    }
  }
}
```

Error Response
```javascript
{
  "success": false,
  "data": {
    "name": "Not Found",
    "message": "Product is not available.",
    "code": 0,
    "status": 404
  }
}
```

---

Order cart is based on IP address and user agent. <br>
As long as those variables stay the same, order can be added onto the same cart.

#### Adding product to cart
```javascript
GET /v1/catalogues/1
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "id": 13,
    "ordered_at": "",
    "customer": null,
    "coupon": "",
    "payment": "",
    "payment_proof": null,
    "discount_percentage": null,
    "discount_amount": null,
    "sub_total": "56700",
    "grand_total": "56700",
    "status": "In Cart",
    "lines": [{
      "id": 14,
      "product": "Product A",
      "quantity": 1,
      "price": 56700,
      "amount": 56700
    }],
    "shipments": []
  }
}
```

Product quantity is increased automatically by 1 and any subsequent order on the same product will increase it automatically by 1.
Different product added to the cart will create a new order line on the cart.


Error Response
```javascript
{
  "success": false,
  "data": {
    "name": "Not Found",
    "message": "Product is not available.",
    "code": 0,
    "status": 404
  }
}
```

#### Order Checkout
```javascript
GET /v1/checkouts
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "id": 13,
    "ordered_at": "",
    "customer": null,
    "coupon": "",
    "payment": "",
    "payment_proof": "",
    "discount_percentage": 0,
    "discount_amount": 0,
    "sub_total": "599960",
    "grand_total": "599960",
    "status": "In Cart",
    "lines": [{
      "id": 14,
      "product": "ProductA",
      "quantity": 1,
      "price": 56700,
      "amount": 56700
    }, {
      "id": 15,
      "product": "B",
      "quantity": 1,
      "price": 543260,
      "amount": 543260
    }],
    "shipments": []
  }
}
```

Empty Cart
```javascript
{
  "success": false,
  "data": {
    "name": "Not Found",
    "message": "Cart is empty!",
    "code": 0,
    "status": 404
  }
}
```

#### Submit Order

When submitting order, here are the conditions:
- customer name, email, phone, and address are required.
- optional coupon code, if provided it will be validated upon submitting the order.
- optional payment type, it's default to `Bank Transfer`

```javascript
POST /v1/checkouts
{
    "name":"Customer A",
    "phone":085,
    "email":customer@timicron.com,
    "address":"Jl.",
    "coupon":"",
    "payment":1
}
```

Success Response
```javascript
{
  "success": true,
  "data": {
    "id": 13,
    "ordered_at": "2018-01-15 23:47:26",
    "customer": {
      "id": 3,
      "name": "Customer A",
      "phone": "085",
      "email": "customer@timicron.com",
      "address": "\"Jl.\"",
      "created_at": "2018-01-15 23:47:25",
      "updated_at": "2018-01-15 23:47:25"
    },
    "coupon": "",
    "payment": "Bank Transfer",
    "payment_proof": "",
    "discount_percentage": 0,
    "discount_amount": 0,
    "sub_total": "599960",
    "grand_total": "599960",
    "status": "Submitted",
    "lines": [{
      "id": 14,
      "product": "Product A",
      "quantity": 1,
      "price": 56700,
      "amount": 56700
    }, {
      "id": 15,
      "product": "B",
      "quantity": 1,
      "price": 543260,
      "amount": 543260
    }],
    "shipments": []
  }
}
```

Error Response
```javascript
{
  "success": false,
  "data": {
    "name": "Not Found",
    "message": "Cart is empty.",
    "code": 0,
    "status": 404
  }
}
```

#### Payment Type List

Displays all available payment methods:
```javascript
GET /v1/checkouts/payments
```

Success Response
```javascript
{
  "success": true,
  "data": [{
    "id": 1,
    "name": "Bank Transfer"
  }]
}
```

Empty Response
```javascript
{
  "success": true,
  "data": []
}
```

#### Payment Confirmation
```javascript
POST /v1/checkouts/payments/13
{
    "file" : "/path/to/payment-proof.png"
}
```

Acceptable extensions are `png`, `jpg`, `jpeg`, `bmp`, `gif`.

Success Response
```javascript
{
    "success":true
    "data": {
        "id":13,
        "ordered_at":"2018-01-15 23:47:26",
        "customer":{
            "id":3,
            "name":"Customer A",
            "phone":"085",
            "email":"customer@timicron.com",
            "address":"\"Jl.\"",
            "created_at":"2018-01-15 23:47:25",
            "updated_at":"2018-01-15 23:47:25"
        },
        "coupon":"",
        "payment":"Bank Transfer",
        "payment_proof":"/server/root/web/uploads/payment-proof.png",
        "discount_percentage":0,
        "discount_amount":0,
        "sub_total":"599960",
        "grand_total":"599960",
        "status":"Paid",
        "lines":[{
            "id":14,
            "product":"Product A",
            "quantity":1,
            "price":56700,
            "amount":56700
        },{
            "id":15,
            "product":"B",
            "quantity":1,
            "price":543260,
            "amount":543260
        }],
        "shipments":[]
    }
}
```

Error Response
```javascript
{
    "success":false,
    "data":{
        "name":"Not Found",
        "message":"Order transaction ID#13 is not available.",
        "code":0,
        "status":404
    }
}
```
-----

### Order Administration

#### Order listing
```javascript
GET /v1/admin/orders?access_token=bc7ad31601acb6bb16d026efa524e33c
```
