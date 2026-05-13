@extends('layouts.app')

@section('title', 'Taisyklės – Kubiliuks')

@section('content')
<section class="bg-white border-t border-black/5">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
        <div class="max-w-4xl">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                Svarbi informacija klientams
            </p>

            <h1 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-stone-900">
                Taisyklės
            </h1>

            <p class="mt-5 text-base sm:text-lg leading-8 text-stone-600">
                Šiame puslapyje pateikiama pagrindinė informacija apie pirkimą, apmokėjimą,
                pristatymą ir grąžinimą. Prieš pateikdami užsakymą klientai kviečiami
                susipažinti su žemiau nurodytomis sąlygomis.
            </p>
        </div>

        <div class="mt-10 rounded-3xl border border-stone-200 bg-stone-50 p-6 sm:p-8">
            <h2 class="text-xl sm:text-2xl font-bold text-stone-900">Pardavėjo rekvizitai</h2>

            <div class="mt-5 space-y-3 text-[15px] sm:text-base leading-8 text-stone-700">
                <div>
                    <span class="font-semibold text-stone-900">Prekės ženklas:</span>
                    Kubiliuks
                </div>
                <div>
                    <span class="font-semibold text-stone-900">Pardavėja:</span>
                    Žaneta Selemonavičė
                </div>
                <div>
                    <span class="font-semibold text-stone-900">Veikla:</span>
                    individuali veikla
                </div>
                <div>
                    <span class="font-semibold text-stone-900">Individualios veiklos pažymos Nr.:</span>
                    1014392
                </div>
                <div>
                    <span class="font-semibold text-stone-900">Adresas:</span>
                    Pamatlindžių g. 5-2, Pamatlindžių k., Kelmės r. sav., Lietuva
                </div>
                <div>
                    <span class="font-semibold text-stone-900">El. paštas:</span>
                    <a href="mailto:info@kubiliuks.lt" class="text-emerald-700 hover:text-emerald-800">
                        info@kubiliuks.lt
                    </a>
                </div>
                <div>
                    <span class="font-semibold text-stone-900">Telefonas:</span>
                    <a href="tel:+37068450267" class="text-emerald-700 hover:text-emerald-800">
                        +370 684 50267
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-10 max-w-4xl space-y-8 text-[15px] sm:text-base leading-8 text-stone-700">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">1. Pirkimo–pardavimo taisyklės</h2>

                <p class="mt-3">
                    Interneto svetainėje pateikta informacija skirta supažindinti klientą su siūlomomis prekėmis,
                    jų kainomis ir užsakymo pateikimo tvarka. Klientas, pateikdamas užsakymą, patvirtina, kad
                    pateikta informacija yra teisinga ir pakankama užsakymo vykdymui.
                </p>

                <p class="mt-3">
                    Pardavėja pasilieka teisę susisiekti su klientu dėl užsakymo patikslinimo, kainos,
                    gamybos termino, pristatymo sąlygų ar kitų svarbių detalių, jei tai reikalinga
                    tinkamam užsakymo įvykdymui.
                </p>

                <p class="mt-3">
                    Užsakymas laikomas priimtu, kai klientas pateikia užsakymą svetainėje ir su juo,
                    jei reikia, suderinamos papildomos užsakymo vykdymo detalės.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">2. Kainos ir apmokėjimas</h2>

                <p class="mt-3">
                    Svetainėje nurodytos kainos pateikiamos eurais. Kai kuriais atvejais galutinė suma gali būti
                    tikslinama individualiai, jei užsakymas susijęs su papildomais pageidavimais, nestandartiniu
                    gaminiu arba specifiniu pristatymu.
                </p>

                <p class="mt-3">
                    Klientas gali pasirinkti svetainėje siūlomą atsiskaitymo būdą. Kai taikoma, apmokėjimas gali būti
                    atliekamas per išorinę mokėjimų sistemą arba pagal individualiai suderintą apmokėjimo informaciją.
                </p>

                <p class="mt-3">
                    Užsakymas gali būti pradėtas vykdyti tik gavus apmokėjimą arba atskirai su klientu suderinus
                    kitą atsiskaitymo tvarką.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">3. Pristatymo sąlygos</h2>

                <p class="mt-3">
                    Prekės pristatomos Lietuvoje. Konkretus pristatymo terminas priklauso nuo užsakomos prekės,
                    jos kiekio, gamybos termino ir pristatymo vietos.
                </p>

                <p class="mt-3">
                    Įprastai pristatymo terminas gali siekti nuo 3 iki 6 savaičių, tačiau tiksli informacija klientui
                    pateikiama prieš galutinai pradedant vykdyti užsakymą.
                </p>

                <p class="mt-3">
                    Pristatymo kaina priklauso nuo vietos, prekės dydžio ir kitų logistinių aplinkybių.
                    Jei reikia, pardavėja susisiekia su klientu dėl pristatymo detalių suderinimo.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">4. Grąžinimo taisyklės</h2>

                <p class="mt-3">
                    Jei klientas įsigyja standartinę prekę, klausimai dėl grąžinimo sprendžiami individualiai,
                    atsižvelgiant į prekės būklę, naudojimą ir faktines aplinkybes.
                </p>

                <p class="mt-3">
                    Jei užsakoma individualiai gaminama arba pagal kliento pageidavimus pritaikyta prekė,
                    tokio užsakymo atšaukimo ar pinigų grąžinimo galimybė gali būti ribota, jei gamyba jau pradėta
                    arba prekė gaminama specialiai klientui.
                </p>

                <p class="mt-3">
                    Kilus klausimams dėl grąžinimo ar užsakymo pakeitimo, klientas turi kuo greičiau susisiekti
                    su pardavėja nurodytais kontaktais. Kiekviena situacija vertinama individualiai.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">5. Kliento atsakomybė</h2>

                <p class="mt-3">
                    Klientas privalo pateikti teisingą informaciją apie save, pristatymo adresą ir kitus duomenis,
                    reikalingus užsakymo vykdymui. Jei pateikti duomenys netikslūs, užsakymo įvykdymas gali užtrukti.
                </p>
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-stone-900">6. Baigiamosios nuostatos</h2>

                <p class="mt-3">
                    Pardavėja pasilieka teisę atnaujinti šį puslapį, jei keičiasi svetainės funkcionalumas,
                    atsiskaitymo būdai ar užsakymų vykdymo tvarka. Naujausia informacija visada skelbiama šiame puslapyje.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection