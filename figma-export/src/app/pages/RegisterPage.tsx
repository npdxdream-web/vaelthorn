import { useState } from "react";
import { Check } from "lucide-react";
import { cities } from "../data/mockData";
import { Button } from "../components/ui/button";
import { Input } from "../components/ui/input";
import { Label } from "../components/ui/label";
import { Textarea } from "../components/ui/textarea";
import { ImageWithFallback } from "../components/figma/ImageWithFallback";

const cityImages = {
  ironveil: "https://images.unsplash.com/photo-1591025788510-163f73e9abca?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtZWRpZXZhbCUyMGZvcnRyZXNzJTIwc3RvbmUlMjB3YWxsc3xlbnwxfHx8fDE3ODE0OTE4MzN8MA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral",
  embercrest: "https://images.unsplash.com/photo-1497002961800-ea7dbfe18696?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx2b2xjYW5pYyUyMGxhdmElMjBmaXJlJTIwbW91bnRhaW58ZW58MXx8fHwxNzgxNDkxODMzfDA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral",
  silversong: "https://images.unsplash.com/photo-1719930699151-ea588d6dc563?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb29ubGl0JTIwb2NlYW4lMjBjb2FzdCUyMG5pZ2h0fGVufDF8fHx8MTc4MTQ5MTgzNHww&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral",
  thornhaven: "https://images.unsplash.com/photo-1483982258113-b72862e6cff6?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkZWVwJTIwZm9yZXN0JTIwYW5jaWVudCUyMHRyZWVzfGVufDF8fHx8MTc4MTQ5MTgzNHww&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral",
};

export function RegisterPage() {
  const [selectedCity, setSelectedCity] = useState<string | null>(null);
  const [step, setStep] = useState<'city' | 'character'>('city');

  return (
    <div className="mx-auto max-w-6xl px-6 py-12">
      {step === 'city' ? (
        <>
          <div className="mb-8 text-center">
            <h1 className="font-display mb-3 text-4xl tracking-wide text-[#D4AF37]">
              Choose Your Home City
            </h1>
            <p className="text-lg text-[#a8a6a3]">
              Your city will shape your character's story and determine your starting village.
            </p>
          </div>

          <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
            {cities.map((city) => (
              <button
                key={city.id}
                onClick={() => setSelectedCity(city.id)}
                className={`group relative overflow-hidden rounded-xl border-2 bg-[#1a1a1a] text-left transition-all ${
                  selectedCity === city.id
                    ? 'border-[#D4AF37] shadow-[0_0_30px_rgba(212,175,55,0.3)]'
                    : 'border-[#2a2a2a] hover:border-[#B87333]'
                }`}
              >
                {/* City Image */}
                <div className="relative h-48 overflow-hidden">
                  <ImageWithFallback 
                    src={cityImages[city.id as keyof typeof cityImages]}
                    alt={city.name}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-[#0f0f0f] via-[#0f0f0f]/60 to-transparent" />
                  
                  {/* City Icon */}
                  <div 
                    className="absolute left-6 top-6 flex h-16 w-16 items-center justify-center rounded-full border-2 bg-[#1a1a1a]/90 text-3xl backdrop-blur-sm"
                    style={{ borderColor: city.color }}
                  >
                    {city.icon}
                  </div>

                  {/* Selected Check */}
                  {selectedCity === city.id && (
                    <div className="absolute right-6 top-6 flex h-10 w-10 items-center justify-center rounded-full bg-[#D4AF37]">
                      <Check className="h-6 w-6 text-[#0f0f0f]" />
                    </div>
                  )}
                </div>

                {/* City Info */}
                <div className="p-6">
                  <h2 
                    className="font-display mb-2 text-2xl"
                    style={{ color: city.color }}
                  >
                    {city.name}
                  </h2>
                  <p className="mb-4 text-sm text-[#a8a6a3]">
                    {city.description}
                  </p>

                  {/* Villages */}
                  <div className="space-y-2 border-t border-[#2a2a2a] pt-4">
                    <div className="text-xs text-[#686664]">STARTING VILLAGES:</div>
                    {city.villages.map((village) => (
                      <div 
                        key={village.id}
                        className="text-sm text-[#e8e6e3]"
                      >
                        • {village.name}
                      </div>
                    ))}
                  </div>
                </div>
              </button>
            ))}
          </div>

          <div className="mt-8 flex justify-center">
            <Button 
              size="lg"
              disabled={!selectedCity}
              onClick={() => setStep('character')}
              className="bg-[#D4AF37] px-8 text-[#0f0f0f] hover:bg-[#B8941F] disabled:opacity-50"
            >
              Continue to Character Creation
            </Button>
          </div>
        </>
      ) : (
        <>
          <div className="mb-8 text-center">
            <h1 className="font-display mb-3 text-4xl tracking-wide text-[#D4AF37]">
              Create Your Character
            </h1>
            <p className="text-lg text-[#a8a6a3]">
              Tell your story in the world of Thiran.
            </p>
          </div>

          <div className="glow-gold mx-auto max-w-2xl rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-8">
            <div className="space-y-6">
              <div>
                <Label className="mb-2 block text-[#D4AF37]">Character Name</Label>
                <Input 
                  placeholder="Enter your character's name"
                  className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="mb-2 block text-[#D4AF37]">Race</Label>
                  <Input 
                    placeholder="e.g., Human, Elf, Dwarf"
                    className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]"
                  />
                </div>
                <div>
                  <Label className="mb-2 block text-[#D4AF37]">Class</Label>
                  <Input 
                    placeholder="e.g., Warrior, Mage"
                    className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]"
                  />
                </div>
              </div>

              <div>
                <Label className="mb-2 block text-[#D4AF37]">Biography</Label>
                <Textarea 
                  placeholder="Write your character's backstory..."
                  className="min-h-[150px] resize-none border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]"
                />
              </div>

              {selectedCity && (
                <div className="rounded-lg border border-[#2a2a2a] bg-[#141414] p-4">
                  <div className="text-sm text-[#a8a6a3]">Home City</div>
                  <div 
                    className="font-display text-lg"
                    style={{ color: cities.find(c => c.id === selectedCity)?.color }}
                  >
                    {cities.find(c => c.id === selectedCity)?.name}
                  </div>
                </div>
              )}

              <div className="flex gap-3">
                <Button 
                  variant="outline"
                  onClick={() => setStep('city')}
                  className="flex-1 border-[#2a2a2a] text-[#e8e6e3] hover:border-[#D4AF37] hover:bg-transparent"
                >
                  Back
                </Button>
                <Button 
                  className="flex-1 bg-[#D4AF37] text-[#0f0f0f] hover:bg-[#B8941F]"
                >
                  Create Character
                </Button>
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
