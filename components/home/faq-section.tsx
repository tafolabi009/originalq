"use client"

import { useRef, useState } from "react"
import { useInView } from "framer-motion"
import { ChevronDown } from "lucide-react"
import { cn } from "@/lib/utils"

export function FAQSection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, amount: 0.2 })
  const [openIndex, setOpenIndex] = useState<number | null>(0)

  const faqs = [
    {
      number: "01",
      question: "How do I book a teacher?",
      answer:
        "Booking a teacher is simple. Browse our certified tutors, view their profiles, and select the one that best matches your needs. Then, click the 'Book Now' button on their profile to schedule a lesson at your preferred time.",
    },
    {
      number: "02",
      question: "Are the teachers certified?",
      answer:
        "Yes, all our teachers are certified Quran instructors with ijazah (certification) in Quran recitation. Many have degrees in Islamic studies and years of teaching experience.",
    },
    {
      number: "03",
      question: "Can I choose the class timing?",
      answer:
        "Yes, you can select class times that work best for your schedule. Our platform allows you to view teacher availability and book lessons at your convenience.",
    },
    {
      number: "04",
      question: "Do you offer free trial classes?",
      answer:
        "Yes, many of our teachers offer a free trial lesson so you can experience their teaching style before committing to paid lessons.",
    },
  ]

  const toggleFaq = (index: number) => {
    setOpenIndex(openIndex === index ? null : index)
  }

  return (
    <section ref={ref} className="py-16 px-6 bg-white relative">
      <div className="max-w-4xl mx-auto">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold text-[#0D6A61] mb-4">Frequently Asked Questions</h2>
          <p className="text-lg text-gray-600 max-w-3xl mx-auto">
            Find answers to common questions about our platform, teachers, and learning process.
          </p>
        </div>

        <div className="space-y-4">
          {faqs.map((faq, index) => (
            <div
              key={index}
              className={cn("bg-[#FFF8E7] rounded-lg overflow-hidden", openIndex === index ? "shadow-md" : "")}
            >
              <button
                className="w-full px-6 py-4 text-left flex items-center justify-between"
                onClick={() => toggleFaq(index)}
                aria-expanded={openIndex === index}
                aria-controls={`faq-answer-${index}`}
              >
                <div className="flex items-center">
                  <span className="text-2xl text-[#0D9488] font-light mr-4">{faq.number}</span>
                  <h3 className="text-lg font-medium text-[#0D6A61]">{faq.question}</h3>
                </div>
                <div className="flex-shrink-0">
                  <ChevronDown
                    className={`h-5 w-5 text-[#0D9488] transition-transform duration-200 ${
                      openIndex === index ? "transform rotate-180" : ""
                    }`}
                  />
                </div>
              </button>

              <div
                id={`faq-answer-${index}`}
                className={cn(
                  "overflow-hidden transition-all duration-300",
                  openIndex === index ? "max-h-96" : "max-h-0",
                )}
              >
                <div className="px-6 pb-4 text-gray-600 pl-16">{faq.answer}</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}

