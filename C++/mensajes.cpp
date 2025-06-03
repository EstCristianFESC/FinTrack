#include <iostream>
#include <cstdlib>
#include <ctime>

int main() {
    std::string mensajes[] = {
        "¡Buen trabajo, no solo los cafés te mantienen vivo!",
        "Sigue así, que hasta la abuela estaría orgullosa.",
        "¿Quién dijo que ahorrar no puede ser divertido? ¡Tú lo estás logrando!",
        "Eres más efectivo que el WiFi cuando funciona bien.",
        "¿Un descanso? Sí, pero solo después de romperla un rato más.",
        "Si fueras un billete, serías un billete ganador.",
        "¡Sigue así y hasta tu perro va a querer consejos financieros!",
        "No te detengas, que el sofá no te va a aplaudir.",
        "Tu cuenta bancaria está a punto de hacerte un high five.",
        "¿Sabías que eres más constante que el sol? ¡Y también más cool!",
        "Eres la prueba de que se puede tener flow y orden en las finanzas.",
        "Si ahorrar fuera un deporte, ya tendrías medalla de oro.",
        "Sigue así, que hasta los memes te aplauden.",
        "Tus finanzas están más en forma que yo después de una maratón.",
        "No aflojes, que el dinero no se va a juntar solo... ¡pero casi!",
        "Eres el héroe anónimo de tu bolsillo.",
        "Si sigues así, hasta el cajero automático te va a saludar.",
        "Estás tan enfocado que hasta el reloj se pone nervioso.",
        "No pares, que el universo financiero está viendo y aplaudiendo.",
        "Tus ahorros ya quieren hacer una fiesta, ¡prepárate!"
    };

    std::srand(std::time(nullptr));
    int index = std::rand() % 20;
    std::cout << mensajes[index] << std::endl;

    return 0;
}