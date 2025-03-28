"use client"

import { useRef } from "react"
import { motion, useInView } from "framer-motion"
import { Check, Clock, BookOpen } from "lucide-react"

export function FeaturesSection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, amount: 0.3 })

  return (
    <section ref={ref} className="py-12 px-6 bg-white">
      <div className="max-w-7xl mx-auto">
        <motion.div
          className="relative rounded-3xl overflow-hidden bg-[#0D6A61] p-8 md:p-12 text-white"
          initial={{ opacity: 0, y: 40 }}
          animate={isInView ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
          transition={{ duration: 0.7 }}
        >
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            <motion.div
              className="flex items-start gap-4"
              initial={{ opacity: 0, x: -20 }}
              animate={isInView ? { opacity: 1, x: 0 } : { opacity: 0, x: -20 }}
              transition={{ duration: 0.5, delay: 0.1 }}
            >
              <div className="bg-white/10 p-3 rounded-full">
                <Check className="h-6 w-6" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-white mb-2">Verified Tutors</h3>
                <p className="text-white/80">Learn from certified and experienced Quran teachers.</p>
              </div>
            </motion.div>

            <motion.div
              className="flex items-start gap-4 border-l border-r border-white/20 px-8"
              initial={{ opacity: 0, y: 20 }}
              animate={isInView ? { opacity: 1, y: 0 } : { opacity: 0, y: 20 }}
              transition={{ duration: 0.5, delay: 0.3 }}
            >
              <div className="bg-white/10 p-3 rounded-full">
                <Clock className="h-6 w-6" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-white mb-2">24/7 Availability</h3>
                <p className="text-white/80">Schedule lessons at your convenience, anytime, anywhere.</p>
              </div>
            </motion.div>

            <motion.div
              className="flex items-start gap-4"
              initial={{ opacity: 0, x: 20 }}
              animate={isInView ? { opacity: 1, x: 0 } : { opacity: 0, x: 20 }}
              transition={{ duration: 0.5, delay: 0.5 }}
            >
              <div className="bg-white/10 p-3 rounded-full">
                <BookOpen className="h-6 w-6" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-white mb-2">Tajweed, Hifz & More</h3>
                <p className="text-white/80">Master Quran recitation, memorization, and Islamic studies.</p>
              </div>
            </motion.div>
          </div>
        </motion.div>
      </div>
    </section>
  )
}

