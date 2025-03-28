"use client"

export function TeachingDetailsStep({ formData, onChange, onSubjectChange }) {
  const subjects = [
    { id: "hifz", label: "Hifz" },
    { id: "hadith", label: "Hadith" },
    { id: "tajweed", label: "Tajweed" },
    { id: "fiqh", label: "Fiqh" },
    { id: "tawheed", label: "Tawheed" },
  ]

  const handleCheckboxChange = (e) => {
    onSubjectChange(e.target.value, e.target.checked)
  }

  return (
    <div className="space-y-6">
      <div className="text-center mb-6">
        <h2 className="text-2xl font-bold">Teaching Details</h2>
        <p className="text-gray-600">Your Teaching Expertise</p>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-2">Subjects you teach</label>
        <div className="flex flex-wrap gap-4">
          {subjects.map((subject) => (
            <div key={subject.id} className="flex items-center">
              <input
                id={subject.id}
                name="subjects"
                type="checkbox"
                value={subject.id}
                checked={formData.subjects.includes(subject.id)}
                onChange={handleCheckboxChange}
                className="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
              />
              <label htmlFor={subject.id} className="ml-2 block text-sm text-gray-900">
                {subject.label}
              </label>
            </div>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label htmlFor="experience" className="block text-sm font-medium text-gray-700 mb-1">
            Years of Experience
          </label>
          <select
            id="experience"
            name="experience"
            className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
            value={formData.experience}
            onChange={onChange}
          >
            <option value="">Select one option...</option>
            <option value="0-1">Less than 1 year</option>
            <option value="1-3">1-3 years</option>
            <option value="3-5">3-5 years</option>
            <option value="5-10">5-10 years</option>
            <option value="10+">More than 10 years</option>
          </select>
        </div>

        <div>
          <label htmlFor="qualification" className="block text-sm font-medium text-gray-700 mb-1">
            Qualification
          </label>
          <select
            id="qualification"
            name="qualification"
            className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
            value={formData.qualification}
            onChange={onChange}
          >
            <option value="">Select one option...</option>
            <option value="ijazah">Ijazah in Quran</option>
            <option value="alim">Alim Course</option>
            <option value="bachelor">Bachelor's in Islamic Studies</option>
            <option value="master">Master's in Islamic Studies</option>
            <option value="phd">PhD in Islamic Studies</option>
          </select>
        </div>
      </div>

      <div>
        <label htmlFor="bio" className="block text-sm font-medium text-gray-700 mb-1">
          Introduce Yourself
        </label>
        <p className="text-sm text-gray-500 mb-2">
          Show potential students who you are! Share your teaching experience and passion for education and briefly
          mention your interests and hobbies
        </p>
        <textarea
          id="bio"
          name="bio"
          rows={5}
          placeholder="Write your bio here"
          className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
          value={formData.bio}
          onChange={onChange}
        ></textarea>
      </div>
    </div>
  )
}

