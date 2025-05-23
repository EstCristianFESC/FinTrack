#include <iostream>
#include <cstdlib>
#include <ctime>

int main() {
    std::string mensajes[] = {
        "¡Buen trabajo cerrando el mes!",
        "Sigue así, crack.",
        "Cada peso cuenta, no aflojes.",
        "Eres el rey de las finanzas.",
        "Un día a la vez, vas bien.",
        "¿Un café para celebrar?"
    };

    std::srand(std::time(nullptr));
    int index = std::rand() % 6;
    std::cout << mensajes[index] << std::endl;

    return 0;
}