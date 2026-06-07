import { createClient } from "@supabase/supabase-js";

// Серверный клиент с сервисным ключом — только для server actions / API routes
export const supabaseAdmin = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.SUPABASE_SERVICE_ROLE_KEY!,
  { auth: { persistSession: false } },
);
