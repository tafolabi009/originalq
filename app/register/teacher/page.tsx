"use client"

import { Header } from "@/components/layout/header"
import { Footer } from "@/components/layout/footer"
import { TeacherRegistrationFlow } from "@/components/registration/teacher-registration-flow"

export default function TeacherRegisterPage() {
  return (
    <div className="min-h-screen flex flex-col bg-gray-50">
      <Header />
      <main className="flex-1 py-12">
        <TeacherRegistrationFlow />
      </main>
      <Footer />
    </div>
  )
}

