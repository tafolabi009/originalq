"use client"

export function AvailabilityStep({ formData, onChange, onDayChange, onTimeChange }) {
  const days = [
    { id: "monday", label: "Monday" },
    { id: "tuesday", label: "Tuesday" },
    { id: "wednesday", label: "Wednesday" },
    { id: "thursday", label: "Thursday" },
    { id: "friday", label: "Friday" },
    { id: "saturday", label: "Saturday" },
    { id: "sunday", label: "Sunday" },
  ]

  const handleDayCheckboxChange = (e) => {
    onDayChange(e.target.value, e.target.checked)
  }

  return (
    <div className="space-y-6">
      <div className="text-center mb-6">
        <h2 className="text-2xl font-bold">Availability & Schedule</h2>
        <p className="text-gray-600">Your Teaching Expertise</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label htmlFor="timezone" className="block text-sm font-medium text-gray-700 mb-1">
            Set your Time Zone
          </label>
          <p className="text-xs text-gray-500 mb-2">
            A correct time zone is essential to coordinate lessons with international students
          </p>
          <select
            id="timezone"
            name="timezone"
            className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
            value={formData.timezone}
            onChange={onChange}
          >
            <option value="">Select one option...</option>
            <option value="GMT-8">Pacific Time (GMT-8)</option>
            <option value="GMT-5">Eastern Time (GMT-5)</option>
            <option value="GMT+0">Greenwich Mean Time (GMT+0)</option>
            <option value="GMT+1">Central European Time (GMT+1)</option>
            <option value="GMT+3">Arabian Standard Time (GMT+3)</option>
            <option value="GMT+5:30">Indian Standard Time (GMT+5:30)</option>
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Teaching Mode</label>
          <p className="text-xs text-gray-500 mb-2">Mark if flexible for full-time, 3 holiday for part-time</p>
          <div className="flex gap-4">
            <div className="flex items-center">
              <input
                id="full-time"
                name="teachingMode"
                type="radio"
                value="full-time"
                checked={formData.teachingMode === "full-time"}
                onChange={onChange}
                className="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300"
              />
              <label htmlFor="full-time" className="ml-2 block text-sm text-gray-900">
                Full-Time
              </label>
            </div>
            <div className="flex items-center">
              <input
                id="part-time"
                name="teachingMode"
                type="radio"
                value="part-time"
                checked={formData.teachingMode === "part-time"}
                onChange={onChange}
                className="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300"
              />
              <label htmlFor="part-time" className="ml-2 block text-sm text-gray-900">
                Part-Time
              </label>
            </div>
          </div>
        </div>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-2">Select Your Availability</label>
        <p className="text-xs text-gray-500 mb-2">
          A correct time zone is essential to coordinate lessons with international students
        </p>

        <div className="space-y-4">
          {days.map((day) => (
            <div key={day.id} className="border-b border-gray-200 pb-4 last:border-0">
              <div className="flex items-center mb-2">
                <input
                  id={day.id}
                  name="availableDays"
                  type="checkbox"
                  value={day.id}
                  checked={formData.availableDays.includes(day.id)}
                  onChange={handleDayCheckboxChange}
                  className="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
                />
                <label htmlFor={day.id} className="ml-2 block text-sm font-medium text-gray-900">
                  {day.label}
                </label>
              </div>

              {formData.availableDays.includes(day.id) && (
                <div className="grid grid-cols-2 gap-4 ml-6">
                  <div>
                    <label htmlFor={`${day.id}-from`} className="block text-xs text-gray-500 mb-1">
                      From
                    </label>
                    <select
                      id={`${day.id}-from`}
                      value={formData.availability[day.id]?.from || ""}
                      onChange={(e) => onTimeChange(day.id, "from", e.target.value)}
                      className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
                    >
                      <option value="">Select one option...</option>
                      <option value="08:00">8:00 AM</option>
                      <option value="09:00">9:00 AM</option>
                      <option value="10:00">10:00 AM</option>
                      <option value="11:00">11:00 AM</option>
                      <option value="12:00">12:00 PM</option>
                      <option value="13:00">1:00 PM</option>
                      <option value="14:00">2:00 PM</option>
                      <option value="15:00">3:00 PM</option>
                      <option value="16:00">4:00 PM</option>
                      <option value="17:00">5:00 PM</option>
                      <option value="18:00">6:00 PM</option>
                      <option value="19:00">7:00 PM</option>
                      <option value="20:00">8:00 PM</option>
                    </select>
                  </div>
                  <div>
                    <label htmlFor={`${day.id}-to`} className="block text-xs text-gray-500 mb-1">
                      To
                    </label>
                    <select
                      id={`${day.id}-to`}
                      value={formData.availability[day.id]?.to || ""}
                      onChange={(e) => onTimeChange(day.id, "to", e.target.value)}
                      className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
                    >
                      <option value="">Select one option...</option>
                      <option value="09:00">9:00 AM</option>
                      <option value="10:00">10:00 AM</option>
                      <option value="11:00">11:00 AM</option>
                      <option value="12:00">12:00 PM</option>
                      <option value="13:00">1:00 PM</option>
                      <option value="14:00">2:00 PM</option>
                      <option value="15:00">3:00 PM</option>
                      <option value="16:00">4:00 PM</option>
                      <option value="17:00">5:00 PM</option>
                      <option value="18:00">6:00 PM</option>
                      <option value="19:00">7:00 PM</option>
                      <option value="20:00">8:00 PM</option>
                      <option value="21:00">9:00 PM</option>
                      <option value="22:00">10:00 PM</option>
                    </select>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}

