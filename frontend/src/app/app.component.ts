import {Component, inject} from '@angular/core';
import {NavigationEnd, Router, RouterOutlet} from '@angular/router';
import {initFlowbite} from 'flowbite';
import {FlowbiteService} from './services/flowbite.service';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {

  private router = inject(Router);

  //Cada vez que termina de actualizarse el DOM luego de finalizar el ruteo
  //se inicializa flowbite.
  ngAfterViewInit() {
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        setTimeout(() => {
          initFlowbite();
        });
      }
    });
  }
}
