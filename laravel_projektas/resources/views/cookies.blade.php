@extends('layouts.app')

@section('title', 'Slapukų politika – Kubiliuks')

@section('content')
<section class="bg-white border-t border-black/5">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
        <div class="max-w-4xl">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                Informacija
            </p>

            <h1 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-stone-900">
                Slapukų politika
            </h1>

            <p class="mt-5 text-base sm:text-lg leading-8 text-stone-600">
                Šiame puslapyje paaiškinama, kaip mūsų svetainėje naudojami slapukai, kam jie reikalingi
                ir kokią įtaką jie turi svetainės veikimui.
            </p>
        </div>

        <div class="mt-10 max-w-4xl space-y-8 text-[15px] sm:text-base leading-8 text-stone-700">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">1. Kas yra slapukai</h2>

                <p class="mt-3">
                    Slapukai yra nedideli tekstiniai failai, kurie išsaugomi jūsų naršyklėje ar įrenginyje,
                    kai lankotės interneto svetainėje. Jie padeda svetainei atsiminti tam tikrus pasirinkimus
                    ir užtikrina sklandesnį svetainės veikimą.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">2. Kodėl naudojame slapukus</h2>

                <p class="mt-3">
                    Mūsų svetainėje slapukai naudojami tam, kad būtų užtikrintas pagrindinis funkcionalumas,
                    saugus prisijungimas ir patogus naudojimasis svetaine.
                </p>

                <p class="mt-3">
                    Be slapukų tam tikros dalys gali neveikti taip, kaip turėtų, ypač:
                </p>

                <ul class="mt-4 space-y-2 list-disc pl-5 marker:text-emerald-700">
                    <li>prisijungimo būsena;</li>
                    <li>krepšelio veikimas;</li>
                    <li>sesijos išlaikymas naršymo metu;</li>
                    <li>tam tikrų saugumo funkcijų veikimas.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">3. Kokie slapukai gali būti naudojami</h2>

                <p class="mt-3">
                    Šiuo metu svetainėje daugiausia naudojami būtini ir techniniai slapukai, reikalingi
                    pagrindiniam svetainės veikimui.
                </p>

                <ul class="mt-4 space-y-2 list-disc pl-5 marker:text-emerald-700">
                    <li><span class="font-semibold">Sesijos slapukai</span> – padeda užtikrinti prisijungimo ir naršymo eigą;</li>
                    <li><span class="font-semibold">Krepšelio slapukai</span> – padeda išlaikyti pasirinktų prekių būseną;</li>
                    <li><span class="font-semibold">Saugumo slapukai</span> – padeda apsaugoti svetainės funkcionalumą.</li>
                </ul>

                <p class="mt-4">
                    Jei ateityje svetainėje būtų naudojami papildomi analitiniai ar rinkodaros sprendimai,
                    informacija apie tai būtų atnaujinta šiame puslapyje.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">4. Būtini slapukai</h2>

                <p class="mt-3">
                    Kai kurie slapukai yra būtini tam, kad svetainė apskritai veiktų. Jie naudojami
                    tik funkcionalumui užtikrinti ir be jų tam tikri veiksmai, pavyzdžiui prisijungimas
                    ar užsakymo procesas, gali neveikti tinkamai.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">5. Kaip galite valdyti slapukus</h2>

                <p class="mt-3">
                    Dauguma naršyklių leidžia valdyti, apriboti ar ištrinti slapukus nustatymuose.
                    Tačiau reikia žinoti, kad išjungus būtinuosius slapukus kai kurios svetainės dalys
                    gali veikti netinkamai arba neveikti visai.
                </p>

                <p class="mt-3">
                    Jei norite pašalinti jau išsaugotus slapukus, tai galite padaryti savo naršyklės nustatymuose.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">6. Trečiųjų šalių paslaugos</h2>

                <p class="mt-3">
                    Tam tikrais atvejais svetainės veikimui ar talpinimui gali būti naudojami techniniai
                    trečiųjų šalių sprendimai. Tokiais atvejais tam tikri techniniai duomenys gali būti
                    tvarkomi tiek, kiek tai būtina svetainės veikimui užtikrinti.
                </p>

                <p class="mt-3">
                    Šiuo metu ši slapukų politika orientuota į pagrindinį svetainės funkcionalumą, o ne į
                    plačias rinkodaros ar reklamos technologijas.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">7. Politikos atnaujinimai</h2>

                <p class="mt-3">
                    Jei svetainės funkcionalumas ateityje keisis, ši slapukų politika gali būti atnaujinta.
                    Naujausia versija visada bus skelbiama šiame puslapyje.
                </p>
            </div>

                        <div class="pt-6 border-t border-stone-200">
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">8. Kontaktai</h2>

                <p class="mt-3">
                    Jei turite klausimų dėl slapukų naudojimo, susisiekite:
                </p>

                <div class="mt-4 space-y-3 text-stone-700 leading-8">
                    <div>
                        <span class="font-semibold text-stone-900">Prekės ženklas:</span>
                        Kubiliuks
                    </div>
                    <div>
                        <span class="font-semibold text-stone-900">Pardavėja:</span>
                        Žaneta Selemonavičė
                    </div>
                    <div>
                        <span class="font-semibold text-stone-900">Individualios veiklos pažymos Nr.:</span>
                        1014392
                    </div>
                    <div>
                        <span class="font-semibold text-stone-900">El. paštas:</span>
                        <a href="mailto:info@kubiliuks.lt" class="text-emerald-700 hover:text-emerald-800">
                            info@kubiliuks.lt
                        </a>
                    </div>
                    <div>
                        <span class="font-semibold text-stone-900">Telefonas:</span>
                        +370 684 50267
                    </div>
                    <div>
                        <span class="font-semibold text-stone-900">Adresas:</span>
                        Pamatlindžių g. 5-2, Pamatlindžių k., Kelmės r. sav., Lietuva
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection