#### Skrypty aplikacji
Skrypty znajdują się w katalogu scripts, należy je uruchamiać z uprawnieniami sudo.
Do dyspozycji jest skrypt uruchamiający, aby go użyć `bash run.sh mode`
  
Mode przyjmuje dwie wersje

  - prod
  - dev

Zakończyć działanie codeignitera można za pomocą skryptu stop.sh, który również przyjmuje parametr mode.

Dodawanie kolejki i aktualizowanie kolejki działa z parametrami domyślnymi.
Aby zmienić parametry domyślne należy podać odpowiednie parametry do skryptu bash.
Podanie parametrów jest dowolne. Jednakże do zaktualizowania kolejki wymagane jest podanie id kolejki, które musi być ostatnim parametrem skryptu.

Przykład

      bash update_queue.sh -p 10 1

  Gdzie 10 to liczba personelu a 1 to id kolejki

Możliwe parametry

  - p - personel
  - e - environment (dev / prod) domyślnie dev
  - k - liczba klientów
  - s - prędkość wagonu
  - f - godzina_od (godzina_dwucyfrowo:minuta_dwucyfrowo)
  - t - godzina_do (godzina_dwucyfrowo:minuta_dwucyfrowo)

Oprócz tego można także usunąć i dodać wagon

Aby dodać wagon należy podać id kolejki `bash add_wagon.sh -i 1`

Aby usunąć wagon należy podać id kolejki oraz id wagonu `bash delete_wagon.sh -i 1 -w 1`

W obu przypadkach domyślnym środowiskiem jest dev, można zmienić za pomocą -e dev/prod

### Logi
Logi znajdują się w katalogu writable/logs/api

logi są rozdzielone na development i production. W środku katalogów znajdują się access_logi, oraz error_logi. error_logi uzupełnią się tylko kiedy w aplikacji pojawi się błąd (Exception). Logami związanymi z kolejką zajmuje się aplikacja cli
