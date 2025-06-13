// Create database and collections for e-commerce
db = db.getSiblingDB('ecommerce');

// Create collections
db.createCollection('users');
db.createCollection('products');
db.createCollection('categories');
db.createCollection('orders');
db.createCollection('cart_items');

// Create indexes for better performance
db.users.createIndex({ "email": 1 }, { unique: true });
db.products.createIndex({ "slug": 1 }, { unique: true });
db.products.createIndex({ "category_id": 1 });
db.products.createIndex({ "price": 1 });
db.orders.createIndex({ "user_id": 1 });
db.orders.createIndex({ "status": 1 });
db.cart_items.createIndex({ "user_id": 1 });

// Sample categories
db.categories.insertMany([
    {
        _id: ObjectId(),
        name: "Elektronik",
        slug: "elektronik",
        description: "Elektronik ürünler",
        created_at: new Date(),
        updated_at: new Date()
    },
    {
        _id: ObjectId(),
        name: "Giyim",
        slug: "giyim",
        description: "Giyim ürünleri",
        created_at: new Date(),
        updated_at: new Date()
    },
    {
        _id: ObjectId(),
        name: "Ev & Yaşam",
        slug: "ev-yasam",
        description: "Ev ve yaşam ürünleri",
        created_at: new Date(),
        updated_at: new Date()
    }
]);

print('Database initialized successfully!');