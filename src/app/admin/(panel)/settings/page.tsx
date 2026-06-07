import { supabaseAdmin } from "@/lib/supabase-admin";
import { defaultSettings } from "@/lib/settings";
import SettingsForm from "./SettingsForm";

export const revalidate = 0;

export default async function SettingsPage() {
  const { data, error } = await supabaseAdmin
    .from("restaurant_settings")
    .select("key, value");

  const tableMissing = !!error;

  // Предзаполняем форму дефолтами, поверх — то, что реально лежит в БД
  const stored = Object.fromEntries(
    (data ?? []).filter((r) => r.value).map((r) => [r.key, r.value as string]),
  );
  const settings = { ...defaultSettings, ...stored };

  return (
    <div>
      <div className="mb-7">
        <p className="text-[11px] uppercase tracking-[0.22em] text-brass">
          Ресторан
        </p>
        <h1 className="mt-1.5 font-display text-[30px] font-bold leading-none text-ink">
          Настройки
        </h1>
      </div>

      {tableMissing && (
        <div className="mb-5 rounded-[12px] border border-[#b0482a]/25 bg-[#b0482a]/[0.06] px-4 py-3.5">
          <p className="text-[13px] font-semibold text-[#a8502f]">
            Хранилище настроек ещё не создано
          </p>
          <p className="mt-1 text-[12.5px] leading-snug text-ink/75">
            Ниже показаны значения по умолчанию. Чтобы сохранять изменения, выполните
            один раз SQL-скрипт{" "}
            <code className="rounded bg-black/5 px-1 py-0.5 text-[11.5px]">
              scripts/create-settings-table.sql
            </code>{" "}
            в Supabase → SQL Editor.
          </p>
        </div>
      )}

      <SettingsForm settings={settings} />
    </div>
  );
}
