const express = require('express');
const router = express.Router();
const { protect } = require('../middleware/auth');
const ctrl = require('../controllers/orderController');

router.post('/', protect, ctrl.createOrder);
router.get('/', protect, ctrl.getOrders);
router.get('/:id', protect, ctrl.getOrderById);

module.exports = router;
