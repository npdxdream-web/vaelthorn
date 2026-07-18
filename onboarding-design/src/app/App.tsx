import { useState, useRef, useEffect } from "react";
import { Lock, CheckCircle2, ChevronDown, Scroll, Loader2 } from "lucide-react";
import { motion, AnimatePresence } from "motion/react";

type StageStatus = "locked" | "active" | "done";
type SubmitState = "idle" | "loading" | "done";

interface Stage {
  id: number;
  thaiNumber: string;
  title: string;
  subtitle: string;
  prompt: string;
  placeholder: string;
}

const STAGES: Stage[] = [
  {
    id: 1,
    thaiNumber: "๑",
    title: "ตัวตน",
    subtitle: "เจ้าเป็นใคร มาจากแผ่นดินใด",
    prompt:
      "ก่อนที่ประตูจะเปิด เจ้าต้องเปิดเผยตัวตนของเจ้าให้แก่ผู้พิทักษ์ทราบ บอกเล่าถึงชื่อที่คนรู้จัก ดินแดนที่เจ้าจากมา และเชื้อสายที่หล่อหลอมเจ้าขึ้นมา ไม่ว่าจะเป็นนักรบจากทุ่งกว้าง พ่อมดจากหอคอยสูง หรือผู้พเนจรไร้รากเหง้า",
    placeholder: "เขียนเรื่องราวของเจ้าที่นี่...",
  },
  {
    id: 2,
    thaiNumber: "๒",
    title: "เหตุ",
    subtitle: "อะไรนำพาเจ้ามาสู่ดินแดนนี้",
    prompt:
      "ทุกก้าวย่างมีเหตุ ทุกการเดินทางมีจุดกำเนิด ผู้พิทักษ์ต้องการรู้ว่าสิ่งใดผลักดันให้เจ้าออกเดินทาง ไม่ว่าจะเป็นคำสาป พันธสัญญา ความสูญเสีย หรือเสียงเรียกที่เจ้าเองก็ยังไม่อาจอธิบายได้ บอกเล่าถึงเหตุนั้น",
    placeholder: "เล่าถึงสิ่งที่นำพาเจ้ามาที่นี่...",
  },
  {
    id: 3,
    thaiNumber: "๓",
    title: "ปณิธาน",
    subtitle: "เจ้าหวังจะตามหาสิ่งใด",
    prompt:
      "บทสุดท้าย — หัวใจของการเดินทาง ผู้พิทักษ์จำเป็นต้องรู้ถึงสิ่งที่เจ้าแสวงหา ไม่ว่าจะเป็นสมบัติ คำตอบ ความแค้น ความรัก หรือสิ่งที่ยิ่งใหญ่กว่านั้น เพราะดินแดนนี้จะมอบให้เฉพาะผู้ที่รู้ว่าตนเองต้องการอะไร",
    placeholder: "สิ่งที่เจ้าแสวงหานั้นคืออะไร...",
  },
];

function OrnamentalDivider() {
  return (
    <div className="flex items-center gap-3 my-2">
      <div className="flex-1 h-px bg-gradient-to-r from-transparent to-primary/40" />
      <span className="text-primary/60 text-xs tracking-widest">✦</span>
      <div className="flex-1 h-px bg-gradient-to-l from-transparent to-primary/40" />
    </div>
  );
}

function ProgressTrack({ current }: { current: number }) {
  return (
    <div className="flex items-center gap-0 mb-10">
      {STAGES.map((s, i) => {
        const filled = i < current;
        const active = i === current - 1 || (current === 0 && i === 0);
        return (
          <div key={s.id} className="flex items-center" style={{ flex: i < STAGES.length - 1 ? "1" : "0" }}>
            <div className="relative flex flex-col items-center">
              <div
                className={[
                  "w-8 h-8 rounded-full border-2 flex items-center justify-center text-xs font-bold transition-all duration-500",
                  filled
                    ? "border-primary bg-primary text-primary-foreground shadow-[0_0_12px_rgba(201,162,39,0.5)]"
                    : active
                    ? "border-primary/70 bg-primary/10 text-primary"
                    : "border-border bg-card/50 text-muted-foreground",
                ].join(" ")}
                style={{ fontFamily: "'Cinzel', serif" }}
              >
                {filled ? <CheckCircle2 className="w-4 h-4" /> : s.thaiNumber}
              </div>
              <span
                className={[
                  "absolute -bottom-6 text-[10px] tracking-widest whitespace-nowrap transition-colors duration-500",
                  filled ? "text-primary/80" : active ? "text-primary/60" : "text-muted-foreground/50",
                ].join(" ")}
                style={{ fontFamily: "'Cinzel', serif" }}
              >
                บทที่ {s.thaiNumber}
              </span>
            </div>
            {i < STAGES.length - 1 && (
              <div className="flex-1 mx-1">
                <div className="h-px bg-border overflow-hidden">
                  <div
                    className="h-full bg-primary/60 transition-all duration-700"
                    style={{ width: filled ? "100%" : "0%" }}
                  />
                </div>
              </div>
            )}
          </div>
        );
      })}
    </div>
  );
}

function StageCard({
  stage,
  status,
  answer,
  onAnswerChange,
  onConfirm,
}: {
  stage: Stage;
  status: StageStatus;
  answer: string;
  onAnswerChange: (v: string) => void;
  onConfirm: () => void;
}) {
  const [submitState, setSubmitState] = useState<SubmitState>("idle");
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  useEffect(() => {
    if (status === "active" && textareaRef.current) {
      setTimeout(() => textareaRef.current?.focus(), 500);
    }
  }, [status]);

  useEffect(() => {
    setSubmitState("idle");
  }, [status]);

  async function handleConfirm() {
    if (!answer.trim() || submitState !== "idle") return;
    setSubmitState("loading");
    await new Promise((r) => setTimeout(r, 800));
    setSubmitState("done");
    await new Promise((r) => setTimeout(r, 400));
    onConfirm();
  }

  const isLocked = status === "locked";
  const isActive = status === "active";
  const isDone = status === "done";

  return (
    <motion.div
      layout
      className={[
        "relative border rounded-sm overflow-hidden transition-all duration-500",
        isLocked
          ? "border-border/30 opacity-40"
          : isDone
          ? "border-primary/30 bg-card/60"
          : "border-primary/50 bg-card shadow-[0_0_30px_rgba(201,162,39,0.08)]",
      ].join(" ")}
    >
      {/* Corner ornaments for active */}
      {isActive && (
        <>
          <div className="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-primary/60" />
          <div className="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-primary/60" />
          <div className="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-primary/60" />
          <div className="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-primary/60" />
        </>
      )}

      {/* Header row */}
      <div className="flex items-center gap-4 px-6 py-4">
        <div
          className={[
            "w-10 h-10 rounded-full border flex items-center justify-center text-lg font-bold shrink-0 transition-all duration-500",
            isLocked
              ? "border-border/40 text-muted-foreground/50"
              : isDone
              ? "border-primary/60 text-primary"
              : "border-primary text-primary shadow-[0_0_10px_rgba(201,162,39,0.3)]",
          ].join(" ")}
          style={{ fontFamily: "'Cinzel', serif" }}
        >
          {isLocked ? <Lock className="w-4 h-4" /> : isDone ? <CheckCircle2 className="w-5 h-5" /> : stage.thaiNumber}
        </div>

        <div className="flex-1 min-w-0">
          <div className="flex items-baseline gap-3">
            <span
              className={[
                "text-xs tracking-[0.25em] uppercase transition-colors duration-500",
                isLocked ? "text-muted-foreground/30" : "text-primary/70",
              ].join(" ")}
              style={{ fontFamily: "'Cinzel', serif" }}
            >
              บทที่ {stage.thaiNumber}
            </span>
            <h3
              className={[
                "text-lg font-semibold transition-colors duration-500",
                isLocked ? "text-muted-foreground/30" : isDone ? "text-foreground/90" : "text-foreground",
              ].join(" ")}
              style={{ fontFamily: "'Cinzel', serif" }}
            >
              {isLocked ? "· · · · · · ·" : stage.title}
            </h3>
          </div>
          <p
            className={[
              "text-sm mt-0.5 transition-colors duration-500",
              isLocked ? "text-muted-foreground/20" : isDone ? "text-muted-foreground" : "text-muted-foreground",
            ].join(" ")}
            style={{ fontFamily: "'Lora', serif", fontStyle: "italic" }}
          >
            {isLocked ? "ยังไม่ถึงเวลา..." : stage.subtitle}
          </p>
        </div>

        <div className="shrink-0">
          {isDone && <CheckCircle2 className="w-5 h-5 text-primary/70" />}
          {isLocked && <ChevronDown className="w-4 h-4 text-muted-foreground/30" />}
        </div>
      </div>

      {/* Completed snippet */}
      <AnimatePresence>
        {isDone && answer && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            transition={{ duration: 0.4, ease: [0.4, 0, 0.2, 1] }}
          >
            <div className="px-6 pb-4">
              <OrnamentalDivider />
              <p
                className="text-sm text-muted-foreground/80 line-clamp-2 italic pl-4 border-l border-primary/20 mt-3"
                style={{ fontFamily: "'Lora', serif" }}
              >
                {answer}
              </p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Active content */}
      <AnimatePresence>
        {isActive && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            transition={{ duration: 0.55, ease: [0.4, 0, 0.2, 1] }}
          >
            <div className="px-6 pb-6">
              <OrnamentalDivider />

              <p
                className="text-sm leading-relaxed text-foreground/75 mt-4 mb-5"
                style={{ fontFamily: "'Lora', serif", fontStyle: "italic" }}
              >
                {stage.prompt}
              </p>

              <div className="relative">
                <textarea
                  ref={textareaRef}
                  value={answer}
                  onChange={(e) => onAnswerChange(e.target.value)}
                  placeholder={stage.placeholder}
                  rows={5}
                  className={[
                    "w-full bg-background/80 border rounded-sm px-4 py-3 text-sm text-foreground",
                    "placeholder:text-muted-foreground/40 resize-none outline-none",
                    "transition-all duration-300",
                    "focus:border-primary/60 focus:shadow-[0_0_0_1px_rgba(201,162,39,0.2)]",
                    "border-border/60",
                  ].join(" ")}
                  style={{ fontFamily: "'Lora', serif" }}
                />
                {/* char count */}
                {answer.length > 0 && (
                  <span className="absolute bottom-3 right-3 text-xs text-muted-foreground/40">
                    {answer.length}
                  </span>
                )}
              </div>

              <div className="flex justify-end mt-4">
                <button
                  onClick={handleConfirm}
                  disabled={!answer.trim() || submitState !== "idle"}
                  className={[
                    "relative flex items-center gap-2 px-6 py-2.5 text-sm rounded-sm border transition-all duration-300 tracking-wider",
                    "disabled:opacity-40 disabled:cursor-not-allowed",
                    answer.trim() && submitState === "idle"
                      ? "border-primary/70 text-primary bg-primary/5 hover:bg-primary/15 hover:shadow-[0_0_16px_rgba(201,162,39,0.25)] cursor-pointer"
                      : submitState === "loading"
                      ? "border-primary/50 text-primary/70 bg-primary/5"
                      : "border-border/40 text-muted-foreground/40",
                  ].join(" ")}
                  style={{ fontFamily: "'Cinzel', serif" }}
                >
                  {submitState === "loading" ? (
                    <>
                      <Loader2 className="w-4 h-4 animate-spin" />
                      <span>กำลังบันทึก...</span>
                    </>
                  ) : submitState === "done" ? (
                    <>
                      <CheckCircle2 className="w-4 h-4" />
                      <span>บันทึกแล้ว</span>
                    </>
                  ) : (
                    <>
                      <span>ยืนยันบทนี้</span>
                      <span className="text-primary/50">✦</span>
                    </>
                  )}
                </button>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
}

export default function App() {
  const [currentStage, setCurrentStage] = useState(0); // 0-indexed, 3 = all done
  const [answers, setAnswers] = useState(["", "", ""]);

  function getStatus(idx: number): StageStatus {
    if (idx < currentStage) return "done";
    if (idx === currentStage) return "active";
    return "locked";
  }

  function handleConfirm(idx: number) {
    if (idx < STAGES.length - 1) {
      setCurrentStage(idx + 1);
    } else {
      setCurrentStage(STAGES.length); // all done
    }
  }

  const allDone = currentStage === STAGES.length;

  return (
    <div
      className="min-h-screen bg-background text-foreground flex flex-col"
      style={{
        backgroundImage:
          "radial-gradient(ellipse at 50% 0%, rgba(201,162,39,0.04) 0%, transparent 60%), radial-gradient(ellipse at 80% 100%, rgba(120,70,10,0.06) 0%, transparent 50%)",
      }}
    >
      {/* Noise texture overlay */}
      <div
        className="fixed inset-0 pointer-events-none opacity-[0.025]"
        style={{
          backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E")`,
          backgroundSize: "200px 200px",
        }}
      />

      <div className="relative z-10 flex flex-col items-center w-full px-4 py-12 sm:py-16 md:py-20">
        <div className="w-full max-w-2xl">
          {/* ── Header ── */}
          <header className="text-center mb-12 md:mb-16">
            <div className="flex justify-center mb-5">
              <div className="w-16 h-16 relative flex items-center justify-center">
                <div className="absolute inset-0 border border-primary/30 rotate-45" />
                <div className="absolute inset-2 border border-primary/15 rotate-45" />
                <Scroll className="w-6 h-6 text-primary/80 relative z-10" />
              </div>
            </div>

            <p
              className="text-xs tracking-[0.4em] uppercase text-primary/60 mb-3"
              style={{ fontFamily: "'Cinzel', serif" }}
            >
              พิธีกรรมแห่งการก้าวผ่าน
            </p>

            <h1
              className="text-3xl sm:text-4xl md:text-5xl font-bold text-foreground mb-4 leading-tight"
              style={{ fontFamily: "'Cinzel', serif" }}
            >
              พิธีเข้าสู่โลก
            </h1>

            <div className="flex items-center justify-center gap-4 mb-5">
              <div className="h-px w-16 bg-gradient-to-r from-transparent to-primary/50" />
              <span className="text-primary/50 text-sm">✦ ✦ ✦</span>
              <div className="h-px w-16 bg-gradient-to-l from-transparent to-primary/50" />
            </div>

            <p
              className="text-sm sm:text-base leading-relaxed text-muted-foreground max-w-lg mx-auto"
              style={{ fontFamily: "'Lora', serif", fontStyle: "italic" }}
            >
              ผู้จะก้าวผ่านประตูมิติต้องสักขีพยานตัวเองต่อหน้าผู้พิทักษ์
              บันทึกเรื่องราวของเจ้าทีละบท เพื่อที่ดินแดนนี้จะจดจำเจ้าได้
            </p>
          </header>

          {/* ── Progress ── */}
          <div className="mb-14 px-2">
            <ProgressTrack current={currentStage} />
          </div>

          {/* ── Stage cards ── */}
          <div className="space-y-4">
            {STAGES.map((stage, idx) => (
              <StageCard
                key={stage.id}
                stage={stage}
                status={getStatus(idx)}
                answer={answers[idx]}
                onAnswerChange={(v) => {
                  const next = [...answers];
                  next[idx] = v;
                  setAnswers(next);
                }}
                onConfirm={() => handleConfirm(idx)}
              />
            ))}
          </div>

          {/* ── Completion seal ── */}
          <AnimatePresence>
            {allDone && (
              <motion.div
                initial={{ opacity: 0, y: 24 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.7, ease: [0.4, 0, 0.2, 1], delay: 0.2 }}
                className="mt-10"
              >
                <div className="relative border border-primary/40 rounded-sm bg-card/80 px-8 py-8 text-center overflow-hidden">
                  {/* Corner ornaments */}
                  <div className="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-primary/50" />
                  <div className="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-primary/50" />
                  <div className="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-primary/50" />
                  <div className="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-primary/50" />

                  <div
                    className="absolute inset-0 opacity-5"
                    style={{
                      backgroundImage:
                        "radial-gradient(ellipse at center, rgba(201,162,39,1) 0%, transparent 70%)",
                    }}
                  />

                  <div className="relative z-10">
                    <div
                      className="w-14 h-14 border-2 border-primary/60 rotate-45 mx-auto mb-5 flex items-center justify-center"
                      style={{ background: "rgba(201,162,39,0.05)" }}
                    >
                      <CheckCircle2 className="w-6 h-6 text-primary -rotate-45" />
                    </div>

                    <p
                      className="text-xs tracking-[0.35em] uppercase text-primary/60 mb-3"
                      style={{ fontFamily: "'Cinzel', serif" }}
                    >
                      บันทึกสมบูรณ์
                    </p>

                    <h2
                      className="text-xl sm:text-2xl font-semibold text-foreground mb-5"
                      style={{ fontFamily: "'Cinzel', serif" }}
                    >
                      เรื่องราวของเจ้าถูกบันทึกแล้ว
                    </h2>

                    <OrnamentalDivider />

                    <p
                      className="text-sm sm:text-base leading-relaxed text-muted-foreground mt-5 max-w-md mx-auto"
                      style={{ fontFamily: "'Lora', serif", fontStyle: "italic" }}
                    >
                      ผู้พิทักษ์แห่งดินแดนได้รับม้วนหนังสือของเจ้าแล้ว
                      พวกเขาจะพิจารณาเรื่องราวและตัดสินใจก่อนที่ประตูจะเปิดรับเจ้า
                      จงรอคอยด้วยความอดทน เพราะการตัดสินใจของผู้พิทักษ์นั้นใช้เวลา...
                    </p>

                    <p
                      className="text-xs text-muted-foreground/50 mt-5 tracking-wider"
                      style={{ fontFamily: "'Cinzel', serif" }}
                    >
                      · รอการพิจารณาจากแอดมิน ·
                    </p>
                  </div>
                </div>
              </motion.div>
            )}
          </AnimatePresence>

          {/* Footer sigil */}
          <div className="text-center mt-16 mb-4">
            <div className="flex items-center justify-center gap-3 opacity-30">
              <div className="h-px w-12 bg-primary" />
              <span className="text-primary text-xs" style={{ fontFamily: "'Cinzel', serif" }}>
                ✦
              </span>
              <div className="h-px w-12 bg-primary" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
