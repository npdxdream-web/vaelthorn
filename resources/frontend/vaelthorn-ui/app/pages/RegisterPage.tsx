import { Button } from "../components/ui/button";
import { Input } from "../components/ui/input";
import { Label } from "../components/ui/label";

export function RegisterPage() {
  return (
    <div className="vaelthorn-page mx-auto max-w-2xl px-6 py-12">
      <div className="mb-8 text-center">
        <h1 className="font-display mb-3 text-4xl tracking-wide text-[#D4AF37]">
          Create Your Character
        </h1>
        <p className="text-lg text-[#a8a6a3]">
          Tell your story in the world of Vaelthorn.
        </p>
      </div>

      <div className="glow-gold rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-8">
        <div className="space-y-6">
          <p className="text-sm font-medium text-[#D4AF37]">ข้อมูลบัญชี</p>

          <div>
            <Label className="mb-2 block text-[#D4AF37]">ชื่อผู้ใช้</Label>
            <Input className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]" />
          </div>
          <div>
            <Label className="mb-2 block text-[#D4AF37]">Email</Label>
            <Input type="email" className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]" />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label className="mb-2 block text-[#D4AF37]">Password</Label>
              <Input type="password" className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]" />
            </div>
            <div>
              <Label className="mb-2 block text-[#D4AF37]">ยืนยัน Password</Label>
              <Input type="password" className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]" />
            </div>
          </div>

          <div className="border-t border-[#2a2a2a] pt-6">
            <p className="mb-4 text-sm font-medium text-[#D4AF37]">ตัวละครของคุณ</p>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label className="mb-2 block text-[#D4AF37]">ชื่อ</Label>
                <Input
                  placeholder="First name"
                  className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]"
                />
              </div>
              <div>
                <Label className="mb-2 block text-[#D4AF37]">นามสกุล</Label>
                <Input
                  placeholder="Last name"
                  className="border-[#2a2a2a] bg-[#141414] text-[#e8e6e3]"
                />
              </div>
            </div>
          </div>

          <Button className="w-full bg-[#D4AF37] py-3 text-base text-[#0f0f0f] hover:bg-[#B8941F]">
            Create Character
          </Button>
        </div>
      </div>
    </div>
  );
}
