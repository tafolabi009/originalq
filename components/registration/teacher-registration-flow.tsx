"use client"

import { useState } from "react"
import { useRouter } from "next/navigation"
import { PersonalInfoStep } from "./personal-info-step"
import { TeachingDetailsStep } from "./teaching-details-step"
import { AvailabilityStep } from "./availability-step"
import { PaymentStep } from "./payment-step"
import { RegistrationSuccess } from "./registration-success"
import { StepIndicator } from "./step-indicator"
import { motion, AnimatePresence } from "framer-motion"

export function TeacherRegistrationFlow() {
  const router = useRouter()
  const [currentStep, setCurrentStep] = useState(1)
  const [direction, setDirection] = useState(0) // -1 for backward, 1 for forward
  const [formData, setFormData] = useState({
    // Personal Info
    name: "",
    phone: "",
    countryCode: "+1",
    country: "",
    city: "",
    profilePhoto: null,

    // Teaching Details
    subjects: [],
    experience: "",
    qualification: "",
    bio: "",

    // Availability
    timezone: "",
    teachingMode: "",
    availableDays: [],
    availability: {},

    // Payment
    currency: "NGN",
    hourlyRate: "",
    paymentMethod: "",
  })

  const steps = ["Personal Information", "Teaching Details", "Availability & Schedule", "Payment & Earnings"]

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target
    setFormData((prev) => ({
      ...prev,
      [name]: type === "checkbox" ? checked : value,
    }))
  }

  const handlePhotoChange = (file) => {
    setFormData((prev) => ({
      ...prev,
      profilePhoto: file,
    }))
  }

  const handleSubjectChange = (subject, checked) => {
    setFormData((prev) => ({
      ...prev,
      subjects: checked ? [...prev.subjects, subject] : prev.subjects.filter((s) => s !== subject),
    }))
  }

  const handleDayChange = (day, checked) => {
    setFormData((prev) => ({
      ...prev,
      availableDays: checked ? [...prev.availableDays, day] : prev.availableDays.filter((d) => d !== day),
      availability: checked
        ? { ...prev.availability, [day]: { from: "", to: "" } }
        : { ...prev.availability, [day]: undefined },
    }))
  }

  const handleTimeChange = (day, type, value) => {
    setFormData((prev) => ({
      ...prev,
      availability: {
        ...prev.availability,
        [day]: {
          ...prev.availability[day],
          [type]: value,
        },
      },
    }))
  }

  const handleCurrencyChange = (currency) => {
    setFormData((prev) => ({
      ...prev,
      currency,
    }))
  }

  const handleNext = () => {
    setDirection(1)
    setCurrentStep((prev) => Math.min(prev + 1, steps.length + 1))
  }

  const handleBack = () => {
    setDirection(-1)
    setCurrentStep((prev) => Math.max(prev - 1, 1))
  }

  const handleSubmit = async () => {
    // In a real implementation, you would submit the form data to your API
    console.log("Form submitted:", formData)

    // Move to success step
    setDirection(1)
    setCurrentStep(steps.length + 1)
  }

  // Animation variants
  const variants = {
    enter: (direction) => ({
      x: direction > 0 ? 100 : -100,
      opacity: 0,
    }),
    center: {
      x: 0,
      opacity: 1,
    },
    exit: (direction) => ({
      x: direction < 0 ? 100 : -100,
      opacity: 0,
    }),
  }

  return (
    <div className="max-w-3xl mx-auto bg-white rounded-lg shadow-sm p-4 sm:p-6 md:p-8">
      {currentStep <= steps.length && <StepIndicator currentStep={currentStep} steps={steps} />}

      <AnimatePresence mode="wait" custom={direction}>
        <motion.div
          key={currentStep}
          custom={direction}
          variants={variants}
          initial="enter"
          animate="center"
          exit="exit"
          transition={{
            x: { type: "spring", stiffness: 300, damping: 30 },
            opacity: { duration: 0.2 },
          }}
        >
          {currentStep === 1 && (
            <PersonalInfoStep formData={formData} onChange={handleChange} onPhotoChange={handlePhotoChange} />
          )}

          {currentStep === 2 && (
            <TeachingDetailsStep formData={formData} onChange={handleChange} onSubjectChange={handleSubjectChange} />
          )}

          {currentStep === 3 && (
            <AvailabilityStep
              formData={formData}
              onChange={handleChange}
              onDayChange={handleDayChange}
              onTimeChange={handleTimeChange}
            />
          )}

          {currentStep === 4 && (
            <PaymentStep formData={formData} onChange={handleChange} onCurrencyChange={handleCurrencyChange} />
          )}

          {currentStep === steps.length + 1 && <RegistrationSuccess name={formData.name || "Teacher"} />}
        </motion.div>
      </AnimatePresence>

      {currentStep <= steps.length && (
        <div className="flex justify-between mt-8">
          {currentStep > 1 ? (
            <button
              onClick={handleBack}
              className="text-teal-600 hover:text-teal-700 transition-colors px-4 py-2 rounded-md hover:bg-teal-50"
            >
              Back
            </button>
          ) : (
            <div></div>
          )}

          {currentStep < steps.length ? (
            <motion.button
              onClick={handleNext}
              className="bg-teal-600 text-white px-6 py-2 rounded-full hover:bg-teal-700 transition-colors"
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
            >
              Save and Continue
            </motion.button>
          ) : (
            <motion.button
              onClick={handleSubmit}
              className="bg-teal-600 text-white px-6 py-2 rounded-full hover:bg-teal-700 transition-colors"
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
            >
              Complete Registration
            </motion.button>
          )}
        </div>
      )}
    </div>
  )
}

