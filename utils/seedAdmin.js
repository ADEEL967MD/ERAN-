// Run manually once: node utils/seedAdmin.js
require('dotenv').config();
const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');
const User = require('../models/User');

(async () => {
  await mongoose.connect(process.env.MONGODB_URI, { dbName: process.env.MONGODB_DATABASE || 'eran_plus' });
  const existing = await User.findOne({ email: process.env.ADMIN_EMAIL });
  if (existing) {
    console.log('Admin already exists');
    process.exit(0);
  }
  const salt = await bcrypt.genSalt(10);
  const passwordHash = await bcrypt.hash(process.env.ADMIN_PASSWORD, salt);
  await User.create({
    name: 'ERAN+ Admin',
    username: 'admin',
    email: process.env.ADMIN_EMAIL,
    phone: '0000000000',
    passwordHash,
    role: 'admin',
    status: 'active'
  });
  console.log('Admin account created');
  process.exit(0);
})();
