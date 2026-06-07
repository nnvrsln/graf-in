import { getSettings } from "@/lib/settings";
import WelcomeView from "./WelcomeView";

export const revalidate = 60;

export default async function WelcomePage() {
  const settings = await getSettings();
  return <WelcomeView settings={settings} />;
}
