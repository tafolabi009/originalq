"use client"

import { useState } from "react"
import Image from "next/image"
import { Search } from "lucide-react"

export function PersonalInfoStep({ formData, onChange, onPhotoChange }) {
  const [photoPreview, setPhotoPreview] = useState(null)

  const handleFileChange = (e) => {
    const file = e.target.files?.[0]
    if (file) {
      onPhotoChange(file)
      const reader = new FileReader()
      reader.onload = () => {
        setPhotoPreview(reader.result)
      }
      reader.readAsDataURL(file)
    }
  }

  return (
    <div className="space-y-6">
      <div className="text-center mb-6">
        <h2 className="text-2xl font-bold">Personal Information</h2>
        <p className="text-gray-600">Tell us about yourself</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
            Name
          </label>
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search className="h-5 w-5 text-gray-400" />
            </div>
            <input
              type="text"
              id="name"
              name="name"
              placeholder="Enter your name"
              className="pl-10 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
              value={formData.name}
              onChange={onChange}
            />
          </div>
        </div>

        <div>
          <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-1">
            Phone Number
          </label>
          <div className="flex">
            <select
              id="countryCode"
              name="countryCode"
              className="rounded-l-md border border-r-0 border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
              value={formData.countryCode}
              onChange={onChange}
            >
              <option value="+1">🇨🇦 +1</option>
              <option value="+44">🇬🇧 +44</option>
              <option value="+91">🇮🇳 +91</option>
              <option value="+971">🇦🇪 +971</option>
            </select>
            <input
              type="tel"
              id="phone"
              name="phone"
              placeholder="Phone Number"
              className="block w-full rounded-r-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
              value={formData.phone}
              onChange={onChange}
            />
          </div>
        </div>

        <div>
          <label htmlFor="country" className="block text-sm font-medium text-gray-700 mb-1">
            Country
          </label>
          <select
            id="country"
            name="country"
            className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
            value={formData.country}
            onChange={onChange}
          >
            <option value="">Select one option...</option>
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="UK">United Kingdom</option>
            <option value="AE">United Arab Emirates</option>
            <option value="SA">Saudi Arabia</option>
          </select>
        </div>

        <div>
          <label htmlFor="city" className="block text-sm font-medium text-gray-700 mb-1">
            City
          </label>
          <select
            id="city"
            name="city"
            className="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500"
            value={formData.city}
            onChange={onChange}
          >
            <option value="">Select one option...</option>
            <option value="New York">New York</option>
            <option value="London">London</option>
            <option value="Dubai">Dubai</option>
            <option value="Toronto">Toronto</option>
          </select>
        </div>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
        <p className="text-sm text-gray-500 mb-2">Choose a photo that will help learners get to know you</p>
        <div className="flex items-center gap-4">
          <div className="w-24 h-24 border border-gray-300 rounded-md bg-gray-100 flex items-center justify-center overflow-hidden">
            {photoPreview ? (
              <Image
                src={photoPreview || "/placeholder.svg"}
                alt="Profile preview"
                width={96}
                height={96}
                className="object-cover"
              />
            ) : (
              <span className="text-xs text-gray-500 text-center">
                JPG or PNG
                <br />
                Max 5MB
              </span>
            )}
          </div>
          <label className="cursor-pointer bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded-md">
            Upload
            <input type="file" className="hidden" accept="image/*" onChange={handleFileChange} />
          </label>
        </div>
      </div>
    </div>
  )
}

