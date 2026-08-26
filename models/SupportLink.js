const mongoose = require('mongoose');

const supportLinkSchema = new mongoose.Schema(
  {
    title: { type: String, required: true },
    url: { type: String, required: true },
    icon: { type: String, default: '' },
    status: { type: String, enum: ['active', 'disabled'], default: 'active' }
  },
  { timestamps: true }
);

module.exports = mongoose.model('SupportLink', supportLinkSchema);
