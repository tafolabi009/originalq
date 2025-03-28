"use client"

export function PaymentStep({ formData, onChange, onCurrencyChange }) {
  return (
    <div className="space-y-6">
      <div className="text-center mb-6">
        <h2 className="text-2xl font-bold">Payment & Earnings</h2>
        <p className="text-gray-600">Set Your Rate & Payment Method</p>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-2">Preferred Currency</label>
        <div className="flex gap-4">
          <div className="flex items-center">
            <input
              id="naira"
              name="currency"
              type="checkbox"
              checked={formData.currency === "NGN"}
              onChange={() => onCurrencyChange("NGN")}
              className="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
            />
            <label htmlFor="naira" className="ml-2 block text-sm text-gray-900">
              Naira
            </label>
          </div>
          <div className="flex items-center">
            <input
              id="dollar"
              name="currency"
              type="checkbox"
              checked={formData.currency === "USD"}
              onChange={() => onCurrencyChange("USD")}
              className="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
            />
            <label htmlFor="dollar" className="ml-2 block text-sm text-gray-900">
              Dollar
            </label>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label htmlFor="hourlyRate" className="block text-sm font-medium text-gray-700 mb-1">
            Minimum & Maximum Hourly Rate
          </label>
          <div className="relative">
            <input
              type="number"
              id="hourlyRate"
              name="hourlyRate"
              placeholder="Input hourly rate"
              className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
              value={formData.hourlyRate}
              onChange={onChange}
              min="1"
            />
          </div>
        </div>

        <div>
          <label htmlFor="paymentMethod" className="block text-sm font-medium text-gray-700 mb-1">
            Payment Method
          </label>
          <select
            id="paymentMethod"
            name="paymentMethod"
            className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
            value={formData.paymentMethod}
            onChange={onChange}
          >
            <option value="">Select payment method</option>
            <option value="bank">Bank Transfer</option>
            <option value="paypal">PayPal</option>
            <option value="payoneer">Payoneer</option>
            <option value="wise">Wise</option>
          </select>
        </div>
      </div>
    </div>
  )
}

