const express = require('express');
const router = express.Router();
const { protect, adminOnly } = require('../middleware/auth');
const upload = require('../middleware/upload');
const ctrl = require('../controllers/adminController');

router.post('/login', ctrl.adminLogin);

router.use(protect, adminOnly);

router.get('/dashboard', ctrl.getDashboardStats);

router.get('/users', ctrl.getUsers);
router.patch('/users/:id/status', ctrl.updateUserStatus);

router.get('/products', ctrl.getAdminProducts);
router.post('/products', upload.single('image'), ctrl.createProduct);
router.patch('/products/:id', upload.single('image'), ctrl.updateProduct);
router.delete('/products/:id', ctrl.deleteProduct);

router.get('/orders', ctrl.getAdminOrders);
router.patch('/orders/:id', ctrl.updateOrderStatus);

router.get('/payments', ctrl.getAdminPayments);

router.get('/settings', ctrl.getSettings);
router.patch('/settings', ctrl.updateSettings);

router.get('/support-links', ctrl.getSupportLinks);
router.post('/support-links', ctrl.createSupportLink);

module.exports = router;
