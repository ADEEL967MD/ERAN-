const Order = require('../models/Order');
const Product = require('../models/Product');

exports.createOrder = async (req, res, next) => {
  try {
    const { items, paymentMethod, shippingAddress, shipping = 0 } = req.body;
    if (!items || !items.length) {
      return res.status(400).json({ success: false, message: 'Order must contain at least one item' });
    }
    let subtotal = 0;
    const orderItems = [];
    for (const item of items) {
      const product = await Product.findById(item.productId);
      if (!product || product.status !== 'active') {
        return res.status(400).json({ success: false, message: `Product not available: ${item.productId}` });
      }
      if (product.stock < item.quantity) {
        return res.status(400).json({ success: false, message: `Insufficient stock for ${product.name}` });
      }
      subtotal += product.price * item.quantity;
      orderItems.push({ productId: product._id, name: product.name, price: product.price, quantity: item.quantity });
      product.stock -= item.quantity;
      await product.save();
    }
    const total = subtotal + Number(shipping);
    const order = await Order.create({
      userId: req.user._id,
      items: orderItems,
      subtotal,
      shipping,
      total,
      paymentMethod,
      shippingAddress
    });
    res.status(201).json({ success: true, order });
  } catch (error) {
    next(error);
  }
};

exports.getOrders = async (req, res, next) => {
  try {
    const orders = await Order.find({ userId: req.user._id }).sort({ createdAt: -1 });
    res.json({ success: true, orders });
  } catch (error) {
    next(error);
  }
};

exports.getOrderById = async (req, res, next) => {
  try {
    const order = await Order.findOne({ _id: req.params.id, userId: req.user._id });
    if (!order) {
      return res.status(404).json({ success: false, message: 'Order not found' });
    }
    res.json({ success: true, order });
  } catch (error) {
    next(error);
  }
};
