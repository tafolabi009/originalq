"use client"

import { CheckCircle } from "lucide-react"

interface StepIndicatorProps {
  currentStep: number
  steps: string[]
}

export function StepIndicator({ currentStep, steps }: StepIndicatorProps) {
  return (
    <div className="mb-8">
      <div className="flex items-center justify-between">
        {steps.map((step, index) => (
          <div key={index} className="flex flex-col items-center">
            <div
              className={`flex items-center justify-center w-10 h-10 rounded-full border-2 ${
                currentStep === index + 1
                  ? "border-teal-600 bg-teal-600 text-white"
                  : currentStep > index + 1
                    ? "border-teal-600 bg-teal-600 text-white"
                    : "border-gray-300 text-gray-500"
              }`}
            >
              {currentStep > index + 1 ? <CheckCircle className="h-5 w-5" /> : index + 1}
            </div>
            <div className="text-xs mt-2 text-gray-500">{step}</div>
          </div>
        ))}
      </div>
      <div className="mt-4 flex justify-between">
        {steps.slice(0, -1).map((_, index) => (
          <div key={index} className={`h-1 w-full ${currentStep > index + 1 ? "bg-teal-600" : "bg-gray-300"}`}></div>
        ))}
      </div>
    </div>
  )
}

