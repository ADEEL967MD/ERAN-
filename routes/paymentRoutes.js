const express = require('express');
const router = express.Router();
const { protect } = require('../middleware/auth');
const ctrl = require('../controllers/paymentController');

router.post('/', protect, ctrl.createPayment);
router.get('/:id', protect, ctrl.getPaymentById);

module.exports = router;
