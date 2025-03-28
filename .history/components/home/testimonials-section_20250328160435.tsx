"use client"

import { useRef, useState } from "react"
import Image from "next/image"
import { useInView } from "framer-motion"
import { ChevronLeft, ChevronRight } from "lucide-react"

export function TestimonialsSection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, amount: 0.2 })
  const [activeIndex, setActiveIndex] = useState(0)

  const testimonials = [
    {
      name: "Aisha K.",
      location: "Canada",
      role: "CEO Eronaman",
      rating: 5,
      quote:
        "Finding a female Quran teacher for my daughter was difficult until I found this platform. Now, she enjoys her Tajweed classes, and has made great progress.",
      image: "/placeholder.svg?height=100&width=100",
    },
    {
      name: "Ahmed S.",
      location: "UK",
      role: "CEO Universal",
      rating: 3,
      quote: "Tajweed lessons are very interactive, and I feel more confident in my recitation.",
      image: "/placeholder.svg?height=100&width=100",
    },
    {
      name: "Bilal R.",
      location: "UAE",
      role: "CEO Universal",
      rating: 5,
      quote: "I always struggled with pronunciation, but my teacher's patience and guidance helped me improve.",
      image: "/placeholder.svg?height=100&width=100",
    },
  ]

  const nextTestimonial = () => {
    setActiveIndex((prev) => (prev + 1) % testimonials.length)
  }

  const prevTestimonial = () => {
    setActiveIndex((prev) => (prev - 1 + testimonials.length) % testimonials.length)
  }

  return (
    <section ref={ref} className="py-16 px-6 bg-white relative">
      {/* Background pattern */}
      <div className="absolute inset-0 z-0">
        
      </div>

      <div className="max-w-7xl mx-auto relative z-10">
        <div className="text-center mb-12">
          <p className="text-sm font-medium text-[#0D9488] uppercase tracking-wider mb-2">TESTIMONIAL</p>
          <h2 className="text-3xl md:text-4xl font-bold text-[#0D6A61] mb-4">What Our Students Say</h2>
        </div>

        <div className="flex justify-center">
          <div className="relative max-w-4xl w-full">
            <div className="flex overflow-hidden">
              {testimonials.map((testimonial, index) => (
                <div
                  key={index}
                  className={`w-full flex-shrink-0 transition-all duration-500 ease-in-out ${
                    index === activeIndex ? "opacity-100" : "opacity-0 absolute"
                  }`}
                  style={{
                    transform: `translateX(${(index - activeIndex) * 100}%)`,
                    display: Math.abs(index - activeIndex) > 1 ? "none" : "block",
                  }}
                >
                  <div className="bg-white p-8 rounded-lg shadow-lg relative">
                    <div className="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-black rounded-full p-4">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                          d="M9.5 8.5L6 12L9.5 15.5M14.5 8.5L18 12L14.5 15.5"
                          stroke="white"
                          strokeWidth="1.5"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                        />
                      </svg>
                    </div>

                    <div className="flex justify-center mb-4 mt-4">
                      {[...Array(5)].map((_, i) => (
                        <svg
                          key={i}
                          className={`w-5 h-5 ${i < testimonial.rating ? "text-orange-400" : "text-gray-300"}`}
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      ))}
                    </div>

                    <p className="text-lg text-gray-700 mb-6 text-center">{testimonial.quote}</p>

                    <div className="flex items-center justify-center">
                      <div className="relative h-12 w-12 rounded-full overflow-hidden mr-4">
                        <Image
                          src={testimonial.image || "/placeholder.svg"}
                          alt={testimonial.name}
                          fill
                          className="object-cover"
                        />
                      </div>
                      <div>
                        <h4 className="font-bold text-gray-900">
                          {testimonial.name}, {testimonial.location}
                        </h4>
                        <p className="text-gray-600">{testimonial.role}</p>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            <div className="flex justify-center mt-8 gap-2">
              <button
                onClick={prevTestimonial}
                className="p-2 rounded-full bg-gray-200 hover:bg-gray-300 transition-colors"
                aria-label="Previous testimonial"
              >
                <ChevronLeft size={20} />
              </button>
              {testimonials.map((_, index) => (
                <button
                  key={index}
                  onClick={() => setActiveIndex(index)}
                  className={`h-2 rounded-full transition-all ${
                    index === activeIndex ? "w-8 bg-[#0D9488]" : "w-2 bg-gray-300"
                  }`}
                  aria-label={`Go to testimonial ${index + 1}`}
                />
              ))}
              <button
                onClick={nextTestimonial}
                className="p-2 rounded-full bg-gray-200 hover:bg-gray-300 transition-colors"
                aria-label="Next testimonial"
              >
                <ChevronRight size={20} />
              </button>
            </div>
          </div>
        </div>

        {/* Quran with beads image */}
        <div className="absolute bottom-0 left-0 w-64 h-64">
          <Image
            src="/images/quran-with-beads.png"
            alt="Quran with prayer beads"
            width={300}
            height={300}
            className="object-contain"
          />
        </div>
      </div>
    </section>
  )
}

