const Payment = require('../models/Payment');
const Order = require('../models/Order');

exports.createPayment = async (req, res, next) => {
  try {
    const { orderId, amount, method, transactionReference } = req.body;
    const order = await Order.findOne({ _id: orderId, userId: req.user._id });
    if (!order) {
      return res.status(404).json({ success: false, message: 'Order not found' });
    }
    const payment = await Payment.create({
      orderId,
      userId: req.user._id,
      amount,
      method,
      transactionReference
    });
    res.status(201).json({ success: true, payment });
  } catch (error) {
    next(error);
  }
};

exports.getPaymentById = async (req, res, next) => {
  try {
    const payment = await Payment.findOne({ _id: req.params.id, userId: req.user._id });
    if (!payment) {
      return res.status(404).json({ success: false, message: 'Payment not found' });
    }
    res.json({ success: true, payment });
  } catch (error) {
    next(error);
  }
};
